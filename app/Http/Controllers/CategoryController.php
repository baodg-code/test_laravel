<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        Category::create($data);

        return redirect()->route('admin.index', ['tab' => 'categories']);
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validatedData($request, $category->id);

        $category->update($data);

        return redirect()->route('admin.index', ['tab' => 'categories']);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.index', ['tab' => 'categories']);
    }

    private function validatedData(Request $request, ?int $categoryId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . ($categoryId ?? 'NULL') . ',id',
            'description' => 'nullable|string|max:255',
        ]);
    }
}
