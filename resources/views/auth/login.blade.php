@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="page page-narrow page-tall">
        <div class="card card-large auth-card">
            <h1>Login</h1>

            <x-form-errors firstOnly="true" />

            <form method="POST" action="{{ route('login.perform') }}">
                @csrf
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button type="submit">Sign in</button>
            </form>

            <div class="hint">
                Admin: admin@cafe.local / admin123<br>
                User: user@cafe.local / user123
            </div>
        </div>
    </div>
@endsection
