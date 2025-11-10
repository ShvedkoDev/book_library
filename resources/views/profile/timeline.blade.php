@extends('layouts.library')

@section('title', 'Activity Timeline - Micronesian Teachers Digital Library')
@section('description', 'View your complete activity timeline in the Micronesian Teachers Digital Library')

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

    .timeline-container {
        position: relative;
    }

    .timeline-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 2rem;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-line {
        position: absolute;
        top: 2rem;
        left: 1.5rem;
        width: 2px;
        height: calc(100% - 2rem);
        background: #e0e0e0;
    }

    .timeline-item:last-child .timeline-line {
        display: none;
    }

    .timeline-content-wrapper {
        display: flex;
        gap: 1rem;
    }

    .timeline-icon-wrapper {
        flex-shrink: 0;
    }

    .timeline-icon {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.25rem;
        color: white;
        position: relative;
        z-index: 1;
    }

    .timeline-icon.rating {
        background: #f39c12;
    }

    .timeline-icon.review {
        background: #007cba;
    }

    .timeline-icon.download {
        background: #28a745;
    }

    .timeline-icon.bookmark {
        background: #8b5cf6;
    }

    .timeline-icon.note {
        background: #fd7e14;
    }

    .timeline-card {
        flex: 1;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.25rem;
        transition: box-shadow 0.3s;
    }

    .timeline-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 0.75rem;
    }

    .timeline-action {
        font-size: 0.95rem;
        color: #555;
        font-weight: 500;
    }

    .timeline-book-title {
        color: #007cba;
        text-decoration: none;
        font-weight: 600;
    }

    .timeline-book-title:hover {
        text-decoration: underline;
    }

    .timeline-time {
        font-size: 0.875rem;
        color: #999;
        white-space: nowrap;
    }

    .timeline-details {
        margin-top: 0.75rem;
    }

    .timeline-stars {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .timeline-stars i {
        font-size: 1rem;
    }

    .timeline-stars i.filled {
        color: #f39c12;
    }

    .timeline-stars i.empty {
        color: #ddd;
    }

    .timeline-stars-value {
        margin-left: 0.5rem;
        font-size: 0.875rem;
        color: #666;
    }

    .timeline-review-text {
        font-size: 0.9rem;
        color: #666;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .timeline-review-status {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .timeline-review-status.pending {
        background: #fff3cd;
        color: #f39c12;
    }

    .timeline-review-status.approved {
        background: #d4edda;
        color: #28a745;
    }

    .timeline-collection {
        font-size: 0.875rem;
        color: #666;
    }

    .timeline-collection strong {
        color: #007cba;
    }

    .timeline-note-text {
        font-size: 0.9rem;
        color: #666;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .timeline-full-time {
        font-size: 0.75rem;
        color: #aaa;
        margin-top: 0.5rem;
    }

    .timeline-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
        padding: 1rem;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }

    .timeline-pagination a,
    .timeline-pagination span {
        padding: 0.5rem 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        text-decoration: none;
        color: #555;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .timeline-pagination a:hover {
        background: #007cba;
        color: white;
        border-color: #007cba;
    }

    .timeline-pagination span {
        background: #f9f9f9;
        cursor: default;
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
</style>
@endpush

@section('content')
<div class="container">
    <div class="activity-header">
        <h1>
            <i class="fas fa-clock" style="color: #6366f1;"></i> Activity Timeline
        </h1>
        <p style="color: #666; margin-top: 0.5rem;">Your complete activity history</p>
    </div>

    <div class="profile-container">
        @include('profile.partials.profile-nav')

        <div class="profile-main">
            <div class="timeline-container">
        @if($paginatedItems->count() > 0)
            <ul class="timeline-list">
                @foreach($paginatedItems as $index => $activity)
                    <li class="timeline-item">
                        @if($index < $paginatedItems->count() - 1)
                            <div class="timeline-line"></div>
                        @endif

                        <div class="timeline-content-wrapper">
                            <div class="timeline-icon-wrapper">
                                @php
                                    $iconClass = match($activity['type']) {
                                        'rating' => 'rating',
                                        'review' => 'review',
                                        'download' => 'download',
                                        'bookmark' => 'bookmark',
                                        'note' => 'note',
                                        default => 'rating'
                                    };

                                    $iconName = match($activity['icon']) {
                                        'star' => 'fa-star',
                                        'chat' => 'fa-comment',
                                        'download' => 'fa-download',
                                        'bookmark' => 'fa-heart',
                                        'pencil' => 'fa-sticky-note',
                                        default => 'fa-circle'
                                    };
                                @endphp
                                <div class="timeline-icon {{ $iconClass }}">
                                    <i class="fas {{ $iconName }}"></i>
                                </div>
                            </div>

                            <div class="timeline-card">
                                <div class="timeline-header">
                                    <div class="timeline-action">
                                        @if($activity['type'] === 'rating')
                                            Rated
                                        @elseif($activity['type'] === 'review')
                                            Reviewed
                                        @elseif($activity['type'] === 'download')
                                            Downloaded
                                        @elseif($activity['type'] === 'bookmark')
                                            Bookmarked
                                        @elseif($activity['type'] === 'note')
                                            Added note to
                                        @endif
                                        <a href="{{ route('library.show', $activity['book']->slug) }}" class="timeline-book-title">
                                            {{ $activity['book']->title }}
                                        </a>
                                    </div>
                                    <div class="timeline-time">
                                        {{ $activity['date']->diffForHumans() }}
                                    </div>
                                </div>

                                @if($activity['type'] === 'rating')
                                    <div class="timeline-details">
                                        <div class="timeline-stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $activity['data']->rating ? 'filled' : 'empty' }}"></i>
                                            @endfor
                                            <span class="timeline-stars-value">{{ $activity['data']->rating }}/5</span>
                                        </div>
                                    </div>
                                @elseif($activity['type'] === 'review')
                                    <div class="timeline-details">
                                        <div class="timeline-review-text">{{ $activity['data']->review_text }}</div>
                                        @if(!$activity['data']->is_approved)
                                            <div class="timeline-review-status pending">
                                                <i class="fas fa-clock"></i> Pending Approval
                                            </div>
                                        @else
                                            <div class="timeline-review-status approved">
                                                <i class="fas fa-check-circle"></i> Approved
                                            </div>
                                        @endif
                                    </div>
                                @elseif($activity['type'] === 'bookmark' && $activity['data']->collection_name)
                                    <div class="timeline-details">
                                        <div class="timeline-collection">
                                            Collection: <strong>{{ $activity['data']->collection_name }}</strong>
                                        </div>
                                    </div>
                                @elseif($activity['type'] === 'note' && $activity['data']->note_text)
                                    <div class="timeline-details">
                                        <div class="timeline-note-text">{{ $activity['data']->note_text }}</div>
                                    </div>
                                @endif

                                <div class="timeline-full-time">
                                    {{ $activity['date']->format('F j, Y \a\t g:i A') }}
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if($total > $perPage)
                <div class="timeline-pagination">
                    @if($currentPage > 1)
                        <a href="?page={{ $currentPage - 1 }}">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    @endif

                    <span>
                        Page {{ $currentPage }} of {{ ceil($total / $perPage) }}
                    </span>

                    @if($currentPage < ceil($total / $perPage))
                        <a href="?page={{ $currentPage + 1 }}">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    @endif
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="fal fa-clock"></i>
                <h2>No activity yet</h2>
                <p>Your activity timeline will appear here as you interact with books.</p>
                <a href="{{ route('library.index') }}" class="btn">Browse Library</a>
            </div>
            @endif
            </div>
        </div>
    </div>
</div>
@endsection
