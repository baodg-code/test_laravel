@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="page page-narrow">
        <div class="card">
            <h1>Users</h1>
            <div class="actions">
                <a class="button" href="{{ route('users.create') }}">+ Add user</a>
                <a class="link" href="{{ route('admin.index') }}">Back to admin</a>
            </div>

            <x-form-errors firstOnly="true" />

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->is_admin ? 'Admin' : 'User' }}</td>
                            <td>
                                <a class="link" href="{{ route('users.edit', $user) }}">Edit</a>
                                <x-inline-delete-form :action="route('users.destroy', $user)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No users.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
