@extends('layouts.app')

@section('title', 'Add User')

@section('content')
    <div class="page page-narrow">
        <div class="card">
            <h1>Add User</h1>

            <x-form-errors />

            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('users._form', ['editing' => false])
                <button type="submit">Save</button>
                <a class="link cancel-link" href="{{ route('admin.index', ['tab' => 'users']) }}">Cancel</a>
            </form>
        </div>
    </div>
@endsection
