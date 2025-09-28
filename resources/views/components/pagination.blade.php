@props(['paginator'])

@if ($paginator->hasPages())
<nav>
    <ul class="pagination justify-content-center pastel-pagination mt-2">
        {{-- Prev --}}
        @if ($paginator->onFirstPage())
        <li class="page-item disabled"><span class="page-link">‹</span></li>
        @else
        <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}">‹</a></li>
        @endif

        {{-- Page numbers --}}
        @for ($page = 1; $page <= $paginator->lastPage(); $page++)
            <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
            </li>
            @endfor

            {{-- Next --}}
            @if ($paginator->hasMorePages())
            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}">›</a></li>
            @else
            <li class="page-item disabled"><span class="page-link">›</span></li>
            @endif
    </ul>
</nav>
@endif