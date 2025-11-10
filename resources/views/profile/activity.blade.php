@extends('layouts.library')

@section('title', 'My Activity - Micronesian Teachers Digital Library')
@section('description', 'View your activity and interactions in the Micronesian Teachers Digital Library')

@push('styles')
<style>
    .activity-dashboard-header {
        padding: 2rem 0;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 2rem;
    }

    .activity-dashboard-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 0.5rem 0;
    }

    .activity-dashboard-header p {
        font-size: 1.1rem;
        color: #666;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        transition: all 0.3s;
        text-decoration: none;
        display: block;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .stat-card-content {
        display: flex;
        align-items: center;
    }

    .stat-card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 1.5rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .stat-card-icon.ratings {
        background: #fff3cd;
        color: #f39c12;
    }

    .stat-card-icon.reviews {
        background: #d1ecf1;
        color: #007cba;
    }

    .stat-card-icon.downloads {
        background: #d4edda;
        color: #28a745;
    }

    .stat-card-icon.bookmarks {
        background: #e6e6fa;
        color: #8b5cf6;
    }

    .stat-card-icon.notes {
        background: #ffe4cc;
        color: #fd7e14;
    }

    .stat-card-icon.timeline {
        background: #e6f3f9;
        color: #6366f1;
    }

    .stat-card-info {
        flex: 1;
    }

    .stat-card-label {
        font-size: 0.875rem;
        color: #666;
        margin-bottom: 0.25rem;
    }

    .stat-card-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #333;
    }

    .stat-card-subtext {
        font-size: 0.875rem;
        color: #999;
    }

    .summary-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 2rem;
    }

    .summary-card h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 1.5rem 0;
    }

    .summary-card p {
        font-size: 1rem;
        color: #666;
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    .summary-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .summary-list li {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.95rem;
        color: #555;
    }

    .summary-list li:last-child {
        border-bottom: none;
    }

    .summary-list li strong {
        color: #007cba;
        font-weight: 600;
    }

    .summary-list li i {
        margin-right: 0.5rem;
        color: #999;
        width: 20px;
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="activity-dashboard-header">
        <h1>
            <i class="fas fa-chart-line"></i> My Activity
        </h1>
        <p>Track your interactions and engagement with the library</p>
    </div>

    <div class="profile-container">
        @include('profile.partials.profile-nav')

        <div class="profile-main">
            <div class="stats-grid">
        <!-- Ratings Card -->
        <a href="{{ route('profile.ratings') }}" class="stat-card">
            <div class="stat-card-content">
                <div class="stat-card-icon ratings">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-card-info">
                    <div class="stat-card-label">Ratings</div>
                    <div class="stat-card-value">{{ $stats['ratings_count'] }}</div>
                </div>
            </div>
        </a>

        <!-- Reviews Card -->
        <a href="{{ route('profile.reviews') }}" class="stat-card">
            <div class="stat-card-content">
                <div class="stat-card-icon reviews">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="stat-card-info">
                    <div class="stat-card-label">Reviews</div>
                    <div class="stat-card-value">{{ $stats['reviews_count'] }}</div>
                </div>
            </div>
        </a>

        <!-- Downloads Card -->
        <a href="{{ route('profile.downloads') }}" class="stat-card">
            <div class="stat-card-content">
                <div class="stat-card-icon downloads">
                    <i class="fas fa-download"></i>
                </div>
                <div class="stat-card-info">
                    <div class="stat-card-label">Downloads</div>
                    <div class="stat-card-value">{{ $stats['downloads_count'] }}</div>
                </div>
            </div>
        </a>

        <!-- Bookmarks Card -->
        <a href="{{ route('profile.bookmarks') }}" class="stat-card">
            <div class="stat-card-content">
                <div class="stat-card-icon bookmarks">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-card-info">
                    <div class="stat-card-label">Bookmarks</div>
                    <div class="stat-card-value">{{ $stats['bookmarks_count'] }}</div>
                </div>
            </div>
        </a>

        <!-- Notes Card -->
        <a href="{{ route('profile.notes') }}" class="stat-card">
            <div class="stat-card-content">
                <div class="stat-card-icon notes">
                    <i class="fas fa-sticky-note"></i>
                </div>
                <div class="stat-card-info">
                    <div class="stat-card-label">Notes</div>
                    <div class="stat-card-value">{{ $stats['notes_count'] }}</div>
                </div>
            </div>
        </a>

        <!-- Timeline Card -->
        <a href="{{ route('profile.timeline') }}" class="stat-card">
            <div class="stat-card-content">
                <div class="stat-card-icon timeline">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-info">
                    <div class="stat-card-label">Activity Timeline</div>
                    <div class="stat-card-subtext">View all activities</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Summary Card -->
    <div class="summary-card">
        <h2><i class="fal fa-chart-bar"></i> Activity Summary</h2>
        <p>
            You've been active in the library! Here's a summary of your interactions:
        </p>
        <ul class="summary-list">
            <li><i class="fal fa-eye"></i> <strong>{{ $stats['views_count'] }}</strong> book views</li>
            <li><i class="fal fa-star"></i> <strong>{{ $stats['ratings_count'] }}</strong> books rated</li>
            <li><i class="fal fa-comment"></i> <strong>{{ $stats['reviews_count'] }}</strong> reviews submitted</li>
            <li><i class="fal fa-download"></i> <strong>{{ $stats['downloads_count'] }}</strong> files downloaded</li>
            <li><i class="fal fa-heart"></i> <strong>{{ $stats['bookmarks_count'] }}</strong> books bookmarked</li>
            <li><i class="fal fa-sticky-note"></i> <strong>{{ $stats['notes_count'] }}</strong> notes created</li>
        </ul>
    </div>
        </div>
    </div>
</div>
@endsection
