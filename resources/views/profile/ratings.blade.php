@extends('layouts.library')

@section('title', 'My Ratings - Activity - Micronesian Teachers Digital Library')
@section('description', 'View all books you have rated in the Micronesian Teachers Digital Library')

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

    .ratings-list {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
    }

    .rating-item {
        padding: 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }

    .rating-item:last-child {
        border-bottom: none;
    }

    .rating-item:hover {
        background: #f9f9f9;
    }

    .rating-item-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .rating-item-title a {
        color: #007cba;
        text-decoration: none;
    }

    .rating-item-title a:hover {
        text-decoration: underline;
    }

    .rating-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        font-size: 0.875rem;
        color: #666;
        margin-bottom: 0.75rem;
    }

    .rating-stars {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.75rem;
    }

    .rating-stars i {
        font-size: 1.25rem;
    }

    .rating-stars i.filled {
        color: #f39c12;
    }

    .rating-stars i.empty {
        color: #ddd;
    }

    .rating-value {
        margin-left: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #555;
    }

    .rating-date {
        color: #999;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .rating-date i {
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
            <a href="{{ route('profile.activity') }}"><i class="fal fa-chart-line"></i> My Activity</a> / Ratings
        </div>
        <h1>
            <i class="fas fa-star" style="color: #f39c12;"></i> My Ratings
        </h1>
        <p style="color: #666; margin-top: 0.5rem;">Books you've rated in the library</p>
    </div>

    @if($ratings->count() > 0)
        <div class="ratings-list">
            @foreach($ratings as $rating)
                <div class="rating-item">
                    <div class="rating-item-title">
                        <a href="{{ route('library.show', $rating->book->slug) }}">
                            {{ $rating->book->title }}
                        </a>
                    </div>

                    <div class="rating-meta">
                        @if($rating->book->publication_year)
                            <span><i class="fal fa-calendar"></i> Published {{ $rating->book->publication_year }}</span>
                        @endif
                    </div>

                    <div class="rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $rating->rating ? 'filled' : 'empty' }}"></i>
                        @endfor
                        <span class="rating-value">{{ $rating->rating }} out of 5</span>
                    </div>

                    <div class="rating-date">
                        <i class="fal fa-clock"></i> Rated {{ $rating->created_at->diffForHumans() }} ({{ $rating->created_at->format('F j, Y') }})
                    </div>
                </div>
            @endforeach
        </div>

        @if($ratings->hasPages())
            <div class="pagination-wrapper">
                {{ $ratings->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="fal fa-star"></i>
            <h2>No ratings yet</h2>
            <p>Start rating books to see them here.</p>
            <a href="{{ route('library.index') }}" class="btn">Browse Library</a>
        </div>
    @endif
</div>
@endsection
