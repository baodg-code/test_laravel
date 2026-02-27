@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="page page-narrow">
        <div class="card">
            <h1>Edit User</h1>

            <x-form-errors />

            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('users._form', ['user' => $user, 'editing' => true])
                <button type="submit">Update</button>
                <a class="link cancel-link" href="{{ route('admin.index', ['tab' => 'users']) }}">Cancel</a>
            </form>
        </div>
    </div>
@endsection
