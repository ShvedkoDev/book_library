@extends('layouts.library')

@section('title', 'My bookmarks - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('description', 'Your saved books and resources from the FSM National Vernacular Language Arts (VLA) Curriculum')

@push('styles')
<style>
    .bookmarks-header {
        padding: 2rem 0;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 2rem;
    }

    .bookmarks-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 0.5rem 0;
    }

    .bookmarks-header p {
        font-size: 1.1rem;
        color: #666;
        margin: 0;
    }

    .bookmarks-count {
        display: inline-block;
        background: #007cba;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        margin-left: 0.5rem;
    }

    .bookmarks-empty {
        text-align: center;
        padding: 4rem 2rem;
    }

    .bookmarks-empty i {
        font-size: 4rem;
        color: #ccc;
        margin-bottom: 1rem;
    }

    .bookmarks-empty h2 {
        font-size: 1.5rem;
        color: #666;
        margin-bottom: 1rem;
    }

    .bookmarks-empty p {
        font-size: 1rem;
        color: #999;
        margin-bottom: 2rem;
    }

    .bookmarks-empty .btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: #007cba;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .bookmarks-empty .btn:hover {
        background: #005a87;
    }

    /* Reuse book card styles from library */
    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
        margin-bottom: 2rem;
    }

    .book-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        transition: box-shadow 0.3s;
        position: relative;
    }

    .book-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .book-card-cover {
        width: 100%;
        border-radius: 4px;
        margin-bottom: 0.75rem;
    }

    .book-card-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        color: #333;
    }

    .book-card-title a {
        color: #333;
        text-decoration: none;
    }

    .book-card-title a:hover {
        color: #007cba;
    }

    .book-card-author {
        font-size: 0.875rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .book-card-meta {
        font-size: 0.8rem;
        color: #999;
        margin-bottom: 0.5rem;
    }

    .book-card-language {
        display: inline-block;
        background: #f0f0f0;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        color: #555;
        margin-bottom: 0.5rem;
    }

    .book-card-saved {
        font-size: 0.75rem;
        color: #999;
        margin-top: 0.5rem;
        margin-bottom: 0.75rem;
        padding-top: 0.5rem;
        border-top: 1px solid #f0f0f0;
    }

    .book-card-saved i {
        margin-right: 0.25rem;
    }

    .book-card-btn {
        width: 100%;
        padding: 0.5rem;
        background-color: #007cba;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.3s;
    }

    .book-card-btn:hover {
        background-color: #005a87;
    }

    .bookmark-remove-btn {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #e0e0e0;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        color: #1d496a;
        font-size: 1rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        padding: 0;
    }

    .bookmark-remove-btn i {
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .bookmark-remove-btn:hover {
        background: #1d496a;
        color: white;
        transform: scale(1.1);
    }

    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .books-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container">
    @if(session('success'))
        <div style="padding: 1rem; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 6px; margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
    @endif

    <div class="bookmarks-header">
        <h1>
            <i class="fas fa-bookmark" style="color: #1d496a;"></i>&nbsp;My bookmarks
            @if($bookmarks->total() > 0)
                <span class="bookmarks-count">{{ $bookmarks->total() }}</span>
            @endif
        </h1>
        <p>Your saved books and resources</p>
    </div>

    @if($bookmarks->isEmpty())
        <div class="bookmarks-empty">
            <i class="fal fa-bookmark"></i>
            <h2>No bookmarks yet</h2>
            <p>Start building your collection by bookmarking books you want to save for later.</p>
            <a href="{{ route('library.index') }}" class="btn">Browse Library</a>
        </div>
    @else
        <div class="books-grid">
            @foreach($bookmarks as $bookmark)
                <div class="book-card">
                    <form action="{{ route('bookmarks.destroy', $bookmark) }}" method="POST" class="bookmark-remove-form" onsubmit="return confirm('Remove this book from your bookmarks?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bookmark-remove-btn" title="Remove bookmark">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </form>

                    <img src="{{ $bookmark->book->getThumbnailUrl() }}"
                         alt="{{ $bookmark->book->title }}"
                         class="book-card-cover">

                    <div class="book-card-title">
                        <a href="{{ route('library.show', $bookmark->book->slug) }}">
                            {{ Str::limit($bookmark->book->title, 50) }}
                        </a>
                    </div>

                    @if($bookmark->book->authors && $bookmark->book->authors->isNotEmpty())
                        <div class="book-card-author">
                            {{ $bookmark->book->authors->pluck('name')->join(', ') }}
                        </div>
                    @endif

                    @php
                        $primaryLang = $bookmark->book->primaryLanguage();
                    @endphp
                    @if($primaryLang)
                        <div class="book-card-language">
                            {{ $primaryLang->name }}
                        </div>
                    @endif

                    <div class="book-card-meta">
                        @if($bookmark->book->publication_year)
                            {{ $bookmark->book->publication_year }}
                        @endif
                    </div>

                    <div class="book-card-saved">
                        <i class="fal fa-clock"></i>
                        Saved {{ $bookmark->created_at->diffForHumans() }}
                    </div>

                    <a href="{{ route('library.show', $bookmark->book->slug) }}" class="button button-primary btn-view" style="display: block; text-decoration: none;">Locate</a>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($bookmarks->hasPages())
            <div class="pagination-wrapper">
                {{ $bookmarks->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
