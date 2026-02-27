@php
    $product = $product ?? null;
@endphp

<div>
    <label>Name</label>
    <input type="text" name="name" value="{{ old('name', $product?->name) }}">
</div>
<div>
    <label>Category</label>
    <select name="category_id">
        @if (!$product)
            <option value="">-- Choose --</option>
        @endif
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" {{ old('category_id', $product?->category_id) == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
</div>
<div>
    <label>Price</label>
    <input type="number" step="0.01" name="price" value="{{ old('price', $product?->price) }}">
</div>
<div>
    <label>Description</label>
    <input type="text" name="description" value="{{ old('description', $product?->description) }}">
</div>
<div>
    <label>Image</label>
    <input type="file" name="image" accept="image/png,image/jpeg">
    @if ($product?->image_path)
        <div class="hint">Current: {{ $product->image_path }}</div>
    @endif
</div>
<div>
    <label class="checkbox-label">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product?->is_active ?? '1') ? 'checked' : '' }}>
        Active
    </label>
</div>