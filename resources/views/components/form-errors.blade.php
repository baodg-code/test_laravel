@props([
    'message' => 'Please fix the errors below.',
    'firstOnly' => false,
])

@if ($errors->any())
    <div class="error-box">
        @if ($firstOnly)
            {{ $errors->first() }}
        @else
            <strong>{{ $message }}</strong>
            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif