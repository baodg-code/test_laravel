@php
    $user = $user ?? null;
    $editing = $editing ?? false;
@endphp

<div>
    <label>Name</label>
    <input type="text" name="name" value="{{ old('name', $user?->name) }}">
</div>
<div>
    <label>Email</label>
    <input type="email" name="email" value="{{ old('email', $user?->email) }}">
</div>
<div>
    <label>{{ $editing ? 'New Password (optional)' : 'Password' }}</label>
    <input type="password" name="password">
</div>
<div>
    <label class="checkbox-label">
        <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user?->is_admin) ? 'checked' : '' }}>
        Admin
    </label>
</div>