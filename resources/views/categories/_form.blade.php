@php
    $category = $category ?? null;
@endphp

<div>
    <label>Name</label>
    <input type="text" name="name" value="{{ old('name', $category?->name) }}">
</div>
<div>
    <label>Description</label>
    <input type="text" name="description" value="{{ old('description', $category?->description) }}">
</div>