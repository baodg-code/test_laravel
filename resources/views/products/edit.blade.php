@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    <div class="page page-narrow">
        <div class="card">
            <h1>Edit Product</h1>

            <x-form-errors />

            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('products._form', ['product' => $product])
                <button type="submit">Update</button>
                <a class="link cancel-link" href="{{ route('admin.index', ['tab' => 'products']) }}">Cancel</a>
            </form>
        </div>
    </div>
@endsection
