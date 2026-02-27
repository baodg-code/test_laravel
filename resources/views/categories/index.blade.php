@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="page page-narrow">
        <div class="card">
            <h1>Categories</h1>
            <div class="actions">
                <a class="button" href="{{ route('categories.create') }}">+ Add category</a>
                <a class="link" href="{{ route('admin.index') }}">Back to admin</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->description ?? '-' }}</td>
                            <td>
                                <a class="link" href="{{ route('categories.edit', $category) }}">Edit</a>
                                <x-inline-delete-form :action="route('categories.destroy', $category)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
