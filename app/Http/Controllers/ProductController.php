<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $query = Product::with('category')->orderBy('name');

        $isAdmin = Auth::user()?->is_admin === true;

        if (!$isAdmin) {
            $query->where('is_active', true);
        }

        $products = $query->get();

        return view('products.index', [
            'products' => $products,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function detail(Product $product)
    {
        return view('product-detail.index', [
            'productId' => $product->id,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $productImageDisk = $this->productImageDisk();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', $productImageDisk);
        }

        Product::create($data);

        return redirect()->route('admin.index', ['tab' => 'products']);
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validatedData($request);
        $productImageDisk = $this->productImageDisk();

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk($productImageDisk)->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', $productImageDisk);
        }

        $product->update($data);

        return redirect()->route('admin.index', ['tab' => 'products']);
    }

    public function destroy(Product $product)
    {
        $productImageDisk = $this->productImageDisk();

        if ($product->image_path) {
            Storage::disk($productImageDisk)->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('admin.index', ['tab' => 'products']);
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'is_active' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
                    $fail('The is active field must be true or false.');
                }
            }],
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function productImageDisk(): string
    {
        return (string) config('filesystems.product_images_disk', 'public');
    }
}
