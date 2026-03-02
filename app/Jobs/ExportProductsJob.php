<?php

namespace App\Jobs;

use App\Exports\ProductsExport;
use App\Models\Product;
use App\Models\ProductExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ExportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $exportId)
    {
    }

    public function handle(): void
    {
        $export = ProductExport::query()->find($this->exportId);

        if (! $export) {
            return;
        }

        $export->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        try {
            $products = Product::query()
                ->with('category:id,name')
                ->orderBy('id')
                ->get();

            $format = $export->format === 'xlsx' ? 'xlsx' : 'csv';

            $writerType = $format === 'xlsx'
                ? 'Xlsx'
                : 'Csv';

            $fileName = 'products_export_'.$export->id.'_'.now()->format('YmdHis').'.'.$format;
            $filePath = 'private/exports/'.$fileName;

            $absoluteDirectory = storage_path('app/private/private/exports');
            File::ensureDirectoryExists($absoluteDirectory, 0755, true);
            @chmod(storage_path('app/private/private'), 0755);
            @chmod($absoluteDirectory, 0755);

            Excel::store(new ProductsExport($products), $filePath, 'local', $writerType);

            $absoluteFilePath = storage_path('app/private/'.$filePath);

            if (is_file($absoluteFilePath)) {
                @chmod($absoluteFilePath, 0644);
            }

            $export->update([
                'status' => 'completed',
                'file_name' => $fileName,
                'file_path' => $filePath,
                'finished_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $export->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            throw $exception;
        }
    }
}
