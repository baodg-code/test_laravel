<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class ProductApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $filters['per_page'] ?? 10;

        $products = Product::query()
            ->with('category:id,name,description')
            ->when(
                !empty($filters['q']),
                fn ($query) => $query->where(function ($subQuery) use ($filters) {
                    $subQuery
                        ->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('description', 'like', '%'.$filters['q'].'%');
                })
            )
            ->when(
                !empty($filters['category_id']),
                fn ($query) => $query->where('category_id', $filters['category_id'])
            )
            ->when(
                isset($filters['min_price']),
                fn ($query) => $query->where('price', '>=', $filters['min_price'])
            )
            ->when(
                isset($filters['max_price']),
                fn ($query) => $query->where('price', '<=', $filters['max_price'])
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);
        $productImageDisk = $this->productImageDisk();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', $productImageDisk);
        }

        $product = Product::create($data)->load('category:id,name');

        return response()->json($this->transformProduct($product), 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load('category:id,name');

        return response()->json($this->transformProduct($product));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $this->validatePayload($request);
        $productImageDisk = $this->productImageDisk();

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk($productImageDisk)->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', $productImageDisk);
        }

        $product->update($data);
        $product->load('category:id,name');

        return response()->json($this->transformProduct($product));
    }

    public function destroy(Product $product): JsonResponse
    {
        $productImageDisk = $this->productImageDisk();

        if ($product->image_path) {
            Storage::disk($productImageDisk)->delete($product->image_path);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted',
        ]);
    }

    private function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function transformProduct(Product $product): array
    {
        $productImageDisk = $this->productImageDisk();
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($productImageDisk);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'description' => $product->description,
            'is_active' => $product->is_active,
            'category_id' => $product->category_id,
            'category' => $product->category,
            'image_path' => $product->image_path,
            'image_url' => $product->image_path ? $disk->url($product->image_path) : null,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];
    }

    private function productImageDisk(): string
    {
        return (string) config('filesystems.product_images_disk', 'public');
    }
}
