@extends('layouts.app')

@section('title', 'Add Category')

@section('content')
    <div class="page page-narrow">
        <div class="card">
            <h1>Add Category</h1>

            <x-form-errors />

            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                @include('categories._form')
                <button type="submit">Save</button>
                <a class="link cancel-link" href="{{ route('admin.index', ['tab' => 'categories']) }}">Cancel</a>
            </form>
        </div>
    </div>
@endsection
