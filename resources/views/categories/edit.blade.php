@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
    <div class="page page-narrow">
        <div class="card">
            <h1>Edit Category</h1>

            <x-form-errors />

            <form method="POST" action="{{ route('categories.update', $category) }}">
                @csrf
                @method('PUT')
                @include('categories._form', ['category' => $category])
                <button type="submit">Update</button>
                <a class="link cancel-link" href="{{ route('admin.index', ['tab' => 'categories']) }}">Cancel</a>
            </form>
        </div>
    </div>
@endsection
