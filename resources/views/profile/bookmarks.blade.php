@extends('layouts.library')

@section('title', 'My Bookmarks - Activity - Micronesian Teachers Digital Library')
@section('description', 'View all books you have bookmarked in the Micronesian Teachers Digital Library')

@push('styles')
<style>
    .activity-header {
        padding: 2rem 0;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 2rem;
    }

    .activity-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 0.5rem 0;
    }

    .activity-header .breadcrumb {
        font-size: 0.95rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .activity-header .breadcrumb a {
        color: #007cba;
        text-decoration: none;
    }

    .activity-header .breadcrumb a:hover {
        text-decoration: underline;
    }

    .bookmarks-list {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
    }

    .bookmark-item {
        padding: 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }

    .bookmark-item:last-child {
        border-bottom: none;
    }

    .bookmark-item:hover {
        background: #f9f9f9;
    }

    .bookmark-item-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .bookmark-item-title a {
        color: #007cba;
        text-decoration: none;
    }

    .bookmark-item-title a:hover {
        text-decoration: underline;
    }

    .bookmark-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
        font-size: 0.875rem;
        color: #666;
    }

    .bookmark-collection {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        background: #e6f3f9;
        color: #007cba;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .bookmark-collection i {
        margin-right: 0.25rem;
    }

    .bookmark-notes {
        margin-top: 0.75rem;
        padding: 0.75rem;
        background: #f9f9f9;
        border-left: 3px solid #007cba;
        border-radius: 4px;
        font-size: 0.9rem;
        color: #555;
    }

    .bookmark-date {
        color: #999;
        font-size: 0.875rem;
    }

    .bookmark-date i {
        margin-right: 0.25rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #ccc;
        margin-bottom: 1rem;
    }

    .empty-state h2 {
        font-size: 1.5rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        font-size: 1rem;
        color: #999;
        margin-bottom: 2rem;
    }

    .empty-state .btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: #007cba;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        transition: background 0.3s;
    }

    .empty-state .btn:hover {
        background: #005a87;
    }

    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="activity-header">
        <div class="breadcrumb">
            <a href="{{ route('profile.activity') }}"><i class="fal fa-chart-line"></i> My Activity</a> / Bookmarks
        </div>
        <h1>
            <i class="fas fa-heart" style="color: #ff6b6b;"></i> My Bookmarks
        </h1>
        <p style="color: #666; margin-top: 0.5rem;">Books you've saved to your collection</p>
    </div>

    @if($bookmarks->count() > 0)
        <div class="bookmarks-list">
            @foreach($bookmarks as $bookmark)
                <div class="bookmark-item">
                    <div class="bookmark-item-title">
                        <a href="{{ route('library.show', $bookmark->book->slug) }}">
                            {{ $bookmark->book->title }}
                        </a>
                    </div>

                    <div class="bookmark-meta">
                        @if($bookmark->collection_name)
                            <span class="bookmark-collection">
                                <i class="fal fa-folder"></i> {{ $bookmark->collection_name }}
                            </span>
                        @endif

                        @if($bookmark->book->publication_year)
                            <span><i class="fal fa-calendar"></i> {{ $bookmark->book->publication_year }}</span>
                        @endif

                        <span class="bookmark-date">
                            <i class="fal fa-clock"></i> Bookmarked {{ $bookmark->created_at->diffForHumans() }}
                        </span>
                    </div>

                    @if($bookmark->notes)
                        <div class="bookmark-notes">
                            <strong><i class="fal fa-sticky-note"></i> Note:</strong> {{ $bookmark->notes }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if($bookmarks->hasPages())
            <div class="pagination-wrapper">
                {{ $bookmarks->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="fal fa-heart"></i>
            <h2>No bookmarks yet</h2>
            <p>Save books for quick access later.</p>
            <a href="{{ route('library.index') }}" class="btn">Browse Library</a>
        </div>
    @endif
</div>
@endsection
