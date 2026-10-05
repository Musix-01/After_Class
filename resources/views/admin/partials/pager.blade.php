{{-- Needs a simplePaginate() result as $items --}}
@if ($items->hasPages())
    <nav class="pager" aria-label="Pages">
        @if ($items->previousPageUrl())
            <a class="btn btn-ghost btn-sm" href="{{ $items->previousPageUrl() }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Previous
            </a>
        @endif
        <span class="pager-page">Page {{ $items->currentPage() }}</span>
        @if ($items->hasMorePages())
            <a class="btn btn-ghost btn-sm" href="{{ $items->nextPageUrl() }}">
                Next <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        @endif
    </nav>
@endif
