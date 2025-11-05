@props(['books', 'title'])

@if($books->isNotEmpty())
    <div class="related-books">
        <h3 class="section-title text-left">{{ $title }}</h3>
        <div class="books-grid">
            @foreach($books as $relatedBook)
                <div class="book-card">
                    <img src="{{ $relatedBook->getThumbnailUrl() }}" alt="{{ $relatedBook->title }}" class="book-card-cover">
                    <div class="book-card-title">{{ Str::limit($relatedBook->title, 50) }}</div>
                    <div class="book-card-author">{{ $relatedBook->creators->pluck('name')->join(', ') }}</div>
                    <div class="book-card-meta">{{ $relatedBook->publication_year }}</div>
                    <a href="{{ route('library.show', $relatedBook->slug) }}" class="book-card-btn">View</a>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if(method_exists($books, 'hasPages') && $books->hasPages())
            <div class="related-books-pagination">
                <div class="pagination-controls">
                    @if($books->onFirstPage())
                        <button class="pagination-btn nav-arrow" disabled>←</button>
                    @else
                        <a href="{{ $books->previousPageUrl() }}" class="pagination-btn nav-arrow">←</a>
                    @endif

                    @php
                        $currentPage = $books->currentPage();
                        $lastPage = $books->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);

                        // Adjust to always show 5 pages if possible
                        if ($end - $start < 4) {
                            if ($start == 1) {
                                $end = min($lastPage, $start + 4);
                            } else {
                                $start = max(1, $end - 4);
                            }
                        }
                    @endphp

                    @if($start > 1)
                        <a href="{{ $books->url(1) }}" class="pagination-btn">1</a>
                        @if($start > 2)
                            <span class="pagination-ellipsis">...</span>
                        @endif
                    @endif

                    @for($page = $start; $page <= $end; $page++)
                        @if($page == $currentPage)
                            <button class="pagination-btn active">{{ $page }}</button>
                        @else
                            <a href="{{ $books->url($page) }}" class="pagination-btn">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="pagination-ellipsis">...</span>
                        @endif
                        <a href="{{ $books->url($lastPage) }}" class="pagination-btn">{{ $lastPage }}</a>
                    @endif

                    @if($books->hasMorePages())
                        <a href="{{ $books->nextPageUrl() }}" class="pagination-btn nav-arrow">→</a>
                    @else
                        <button class="pagination-btn nav-arrow" disabled>→</button>
                    @endif
                </div>

                <div class="pagination-info">
                    Showing {{ $books->firstItem() }} to {{ $books->lastItem() }} of {{ $books->total() }} entries
                </div>
            </div>
        @endif
    </div>
@endif

<style>
.related-books-pagination {
    margin-top: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.pagination-controls {
    display: flex;
    gap: 0.25rem;
    align-items: center;
    flex-wrap: wrap;
    justify-content: center;
}

.pagination-btn {
    min-width: 32px;
    height: 32px;
    padding: 0;
    border: 1px solid transparent;
    background: transparent;
    color: #1e73be;
    text-decoration: none;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.pagination-btn:hover {
    background: rgba(30, 115, 190, 0.1);
    color: #1e73be;
}

.pagination-btn.active {
    background: #1e73be;
    color: white;
    font-weight: 600;
}

.pagination-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.pagination-btn:disabled:hover {
    background: transparent;
}

.pagination-btn.nav-arrow {
    background: #1e73be;
    color: white;
}

.pagination-btn.nav-arrow:hover {
    background: #155a8a;
}

.pagination-ellipsis {
    color: #999;
    padding: 0 0.25rem;
    display: inline-flex;
    align-items: center;
}

.pagination-info {
    font-size: 0.875rem;
    color: #666;
    text-align: center;
}

@media (max-width: 768px) {
    .pagination-controls {
        font-size: 0.75rem;
    }

    .pagination-btn {
        min-width: 28px;
        height: 28px;
        font-size: 0.75rem;
    }
}
</style>
