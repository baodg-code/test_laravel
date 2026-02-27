@props([
    'action',
    'label' => 'Delete',
])

<form method="POST" action="{{ $action }}" class="inline-form">
    @csrf
    @method('DELETE')
    <button class="danger" type="submit">{{ $label }}</button>
</form>