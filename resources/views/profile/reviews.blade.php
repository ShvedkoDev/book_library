@extends('layouts.library')

@section('title', 'My Reviews - Activity - Micronesian Teachers Digital Library')
@section('description', 'View all book reviews you have submitted in the Micronesian Teachers Digital Library')

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

    .reviews-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .review-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        transition: box-shadow 0.3s;
    }

    .review-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .review-title {
        font-size: 1.1rem;
        font-weight: 600;
        flex: 1;
    }

    .review-title a {
        color: #007cba;
        text-decoration: none;
    }

    .review-title a:hover {
        text-decoration: underline;
    }

    .review-status-badges {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge.approved {
        background: #d4edda;
        color: #28a745;
    }

    .status-badge.pending {
        background: #fff3cd;
        color: #f39c12;
    }

    .review-text-box {
        background: #f9f9f9;
        border-left: 3px solid #007cba;
        padding: 1rem;
        border-radius: 4px;
        font-size: 0.95rem;
        color: #555;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .review-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
        color: #999;
    }

    .review-meta i {
        margin-right: 0.25rem;
    }

    .review-time {
        color: #999;
        font-size: 0.875rem;
        margin-top: 0.5rem;
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
            <a href="{{ route('profile.activity') }}"><i class="fal fa-chart-line"></i> My Activity</a> / Reviews
        </div>
        <h1>
            <i class="fas fa-comment" style="color: #007cba;"></i> My Reviews
        </h1>
        <p style="color: #666; margin-top: 0.5rem;">Your book reviews and feedback</p>
    </div>

    @if($reviews->count() > 0)
        <div class="reviews-list">
            @foreach($reviews as $review)
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-title">
                            <a href="{{ route('library.show', $review->book->slug) }}">
                                {{ $review->book->title }}
                            </a>
                        </div>
                        <div class="review-status-badges">
                            @if($review->is_approved)
                                <span class="status-badge approved">
                                    <i class="fas fa-check-circle"></i> Approved
                                </span>
                            @else
                                <span class="status-badge pending">
                                    <i class="fas fa-clock"></i> Pending Approval
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="review-text-box">
                        {{ $review->review_text }}
                    </div>

                    <div class="review-meta">
                        <span>
                            <i class="fal fa-calendar"></i> Submitted on {{ $review->created_at->format('F j, Y') }}
                        </span>
                        @if($review->is_approved && $review->approved_at)
                            <span>
                                <i class="fal fa-check"></i> Approved on {{ $review->approved_at->format('F j, Y') }}
                            </span>
                        @endif
                    </div>

                    <div class="review-time">
                        <i class="fal fa-clock"></i> {{ $review->created_at->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>

        @if($reviews->hasPages())
            <div class="pagination-wrapper">
                {{ $reviews->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="fal fa-comment"></i>
            <h2>No reviews yet</h2>
            <p>Share your thoughts about books you've read.</p>
            <a href="{{ route('library.index') }}" class="btn">Browse Library</a>
        </div>
    @endif
</div>
@endsection
