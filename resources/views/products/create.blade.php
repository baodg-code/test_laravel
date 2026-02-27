@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
    <div class="page page-narrow">
        <div class="card">
            <h1>Add Product</h1>

            <x-form-errors />

            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                @csrf
                @include('products._form')
                <button type="submit">Save</button>
                <a class="link cancel-link" href="{{ route('admin.index', ['tab' => 'products']) }}">Cancel</a>
            </form>
        </div>
    </div>
@endsection
