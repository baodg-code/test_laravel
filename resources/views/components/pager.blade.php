@props([
    'paginator',
])

@if ($paginator->lastPage() > 1)
    <div class="pager">
        @if ($paginator->onFirstPage())
            <span class="pager-btn disabled">«</span>
        @else
            <a href="{{ $paginator->url($paginator->currentPage() - 1) }}" class="pager-btn">«</a>
        @endif

        @for ($page = 1; $page <= $paginator->lastPage(); $page++)
            <a href="{{ $paginator->url($page) }}" class="pager-btn {{ $paginator->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>
        @endfor

        @if ($paginator->currentPage() === $paginator->lastPage())
            <span class="pager-btn disabled">»</span>
        @else
            <a href="{{ $paginator->url($paginator->currentPage() + 1) }}" class="pager-btn">»</a>
        @endif
    </div>
@endif