<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductExportResource;
use App\Jobs\ExportProductsJob;
use App\Models\ProductExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductExportApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'format' => ['nullable', 'in:csv,xlsx'],
        ]);

        $export = ProductExport::create([
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'format' => $data['format'] ?? 'xlsx',
        ]);

        ExportProductsJob::dispatch($export->id);

        return response()->json([
            'message' => 'Export request accepted. Please check status later.',
            'data' => ProductExportResource::make($export),
        ], 202);
    }

    public function show(Request $request, ProductExport $productExport): ProductExportResource
    {
        $this->ensureOwnedByUser($request, $productExport);

        return ProductExportResource::make($productExport);
    }

    public function download(Request $request, ProductExport $productExport)
    {
        $this->ensureOwnedByUser($request, $productExport);

        if ($productExport->status !== 'completed' || ! $productExport->file_path) {
            return response()->json([
                'message' => 'Export file is not ready yet.',
            ], 422);
        }

        $exportDisk = (string) config('filesystems.exports_disk', 'local');
        $storedPath = ltrim((string) $productExport->file_path, '/');
        $normalizedPath = preg_replace('/^private\//', '', $storedPath) ?: '';
        $disk = Storage::disk($exportDisk);

        $candidatePaths = array_filter(array_unique([
            $normalizedPath,
            $storedPath,
            $productExport->file_name ? 'exports/'.$productExport->file_name : null,
            $exportDisk === 'local' && $productExport->file_name ? 'private/exports/'.$productExport->file_name : null,
        ]));

        $resolvedPath = null;

        foreach ($candidatePaths as $candidatePath) {
            if ($disk->exists($candidatePath)) {
                $resolvedPath = $candidatePath;
                break;
            }
        }

        if (! $resolvedPath) {
            return response()->json([
                'message' => 'Export file not found.',
            ], 404);
        }

        if ($productExport->file_path !== $resolvedPath) {
            $productExport->update(['file_path' => $resolvedPath]);
        }

        $extension = strtolower(pathinfo($productExport->file_name ?? '', PATHINFO_EXTENSION));

        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            $extension = $productExport->format === 'xlsx' ? 'xlsx' : 'csv';
        }

        $contentType = $extension === 'xlsx'
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            : 'text/csv';

        $downloadName = $productExport->file_name ?? ('products_export.'.$extension);

        if ($exportDisk === 'local') {
            return response()->download(
                $disk->path($resolvedPath),
                $downloadName,
                ['Content-Type' => $contentType]
            );
        }

        $stream = $disk->readStream($resolvedPath);

        if ($stream === false) {
            return response()->json([
                'message' => 'Export file not found.',
            ], 404);
        }

        return response()->streamDownload(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, $downloadName, [
            'Content-Type' => $contentType,
        ]);
    }

    private function ensureOwnedByUser(Request $request, ProductExport $productExport): void
    {
        if ($productExport->user_id !== $request->user()->id) {
            abort(403, 'You are not allowed to access this export.');
        }
    }
}
