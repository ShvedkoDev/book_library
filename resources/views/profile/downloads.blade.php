@extends('layouts.library')

@section('title', 'My Downloads - Activity - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('description', 'View your complete download history in the FSM National Vernacular Language Arts (VLA) Curriculum')

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

    .downloads-list {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
    }

    .download-item {
        padding: 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }

    .download-item:last-child {
        border-bottom: none;
    }

    .download-item:hover {
        background: #f9f9f9;
    }

    .download-item-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .download-item-title a {
        color: #007cba;
        text-decoration: none;
    }

    .download-item-title a:hover {
        text-decoration: underline;
    }

    .download-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
        font-size: 0.875rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .download-meta i {
        margin-right: 0.25rem;
    }

    .access-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .access-badge.full {
        background: #d4edda;
        color: #28a745;
    }

    .access-badge.limited {
        background: #fff3cd;
        color: #f39c12;
    }

    .access-badge.unavailable {
        background: #f8d7da;
        color: #dc3545;
    }

    .download-date {
        color: #999;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .download-date i {
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
        <h1>
            <i class="fas fa-download" style="color: #28a745;"></i> My downloads
        </h1>
        <p style="color: #666; margin-top: 0.5rem;">Your complete download history</p>
    </div>

    <div class="profile-container">
        @include('profile.partials.profile-nav')

        <div class="profile-main">
            @if($downloads->count() > 0)
        <div class="downloads-list">
            @foreach($downloads as $download)
                <div class="download-item">
                    <div class="download-item-title">
                        <a href="{{ route('library.show', $download->book->slug) }}">
                            {{ $download->book->title }}
                        </a>
                    </div>

                    <div class="download-meta">
                        @if($download->book->publication_year)
                            <span><i class="fal fa-calendar"></i> Published {{ $download->book->publication_year }}</span>
                        @endif

                        @php
                            $accessClass = match($download->book->access_level) {
                                'full' => 'full',
                                'limited' => 'limited',
                                'unavailable' => 'unavailable',
                                default => 'full'
                            };
                        @endphp
                        <span class="access-badge {{ $accessClass }}">
                            @if($download->book->access_level === 'full')
                                <i class="fas fa-check-circle"></i>
                            @elseif($download->book->access_level === 'limited')
                                <i class="fas fa-exclamation-circle"></i>
                            @else
                                <i class="fas fa-lock"></i>
                            @endif
                            {{ ucfirst($download->book->access_level) }} Access
                        </span>
                    </div>

                    <div class="download-date">
                        <i class="fal fa-clock"></i> Downloaded {{ $download->created_at->diffForHumans() }} ({{ $download->created_at->format('F j, Y \a\t g:i A') }})
                    </div>
                </div>
            @endforeach
        </div>

        @if($downloads->hasPages())
            <div class="pagination-wrapper">
                {{ $downloads->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="fal fa-download"></i>
            <h2>No downloads yet</h2>
            <p>Your download history will appear here.</p>
            <a href="{{ route('library.index') }}" class="btn">Browse Library</a>
        </div>
            @endif
        </div>
    </div>
</div>
@endsection
