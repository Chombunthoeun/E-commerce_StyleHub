@if($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        @if($paginator->onFirstPage())
            <span class="pagination__btn is-disabled">&laquo; Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination__btn">&laquo; Prev</a>
        @endif

        <span class="pagination__pages">
            @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" class="pagination__page {{ $page === $paginator->currentPage() ? 'is-active' : '' }}">{{ $page }}</a>
            @endforeach
        </span>

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination__btn">Next &raquo;</a>
        @else
            <span class="pagination__btn is-disabled">Next &raquo;</span>
        @endif
    </nav>
@endif
