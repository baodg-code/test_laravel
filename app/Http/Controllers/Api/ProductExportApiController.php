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

        $storedPath = ltrim((string) $productExport->file_path, '/');
        $normalizedPath = preg_replace('/^private\//', '', $storedPath) ?: '';
        $disk = Storage::disk('local');

        $candidatePaths = array_filter(array_unique([
            $normalizedPath,
            $storedPath,
            $productExport->file_name ? 'exports/'.$productExport->file_name : null,
            $productExport->file_name ? 'private/exports/'.$productExport->file_name : null,
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

        return response()->download(
            $disk->path($resolvedPath),
            $productExport->file_name ?? ('products_export.'.$extension),
            ['Content-Type' => $contentType]
        );
    }

    private function ensureOwnedByUser(Request $request, ProductExport $productExport): void
    {
        if ($productExport->user_id !== $request->user()->id) {
            abort(403, 'You are not allowed to access this export.');
        }
    }
}
