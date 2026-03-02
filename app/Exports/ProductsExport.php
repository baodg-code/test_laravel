<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection; //Should use FromQuery instead of FromCollection if the dataset is large to avoid memory issues
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $products)
    {
    }

    public function collection(): Collection
    {
        return $this->products;
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Category', 'Price', 'Is Active', 'Created At'];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->category?->name,
            (float) $product->price,
            $product->is_active ? 'Yes' : 'No',
            optional($product->created_at)?->toDateTimeString(),
        ];
    }
}
