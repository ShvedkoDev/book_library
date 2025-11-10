@extends('layouts.library')

@section('title', 'My Notes - Activity - Micronesian Teachers Digital Library')
@section('description', 'View all your personal notes on books in the Micronesian Teachers Digital Library')

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

    .notes-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .note-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        transition: box-shadow 0.3s;
    }

    .note-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .note-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .note-title-section {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    }

    .note-title {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .note-title a {
        color: #007cba;
        text-decoration: none;
    }

    .note-title a:hover {
        text-decoration: underline;
    }

    .page-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        background: #e6f3f9;
        color: #007cba;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .note-privacy-section {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .privacy-icon {
        color: #999;
        font-size: 1rem;
    }

    .note-text-box {
        background: #fffbf0;
        border-left: 3px solid #fd7e14;
        padding: 1rem;
        border-radius: 4px;
        font-size: 0.95rem;
        color: #555;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .note-meta {
        font-size: 0.875rem;
        color: #999;
    }

    .note-meta i {
        margin-right: 0.25rem;
    }

    .note-updated {
        color: #666;
        margin-left: 0.5rem;
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
            <i class="fas fa-sticky-note" style="color: #fd7e14;"></i> My Notes
        </h1>
        <p style="color: #666; margin-top: 0.5rem;">Your personal notes and annotations</p>
    </div>

    <div class="profile-container">
        @include('profile.partials.profile-nav')

        <div class="profile-main">
            @if($notes->count() > 0)
                <div class="notes-list">
            @foreach($notes as $note)
                <div class="note-card">
                    <div class="note-header">
                        <div class="note-title-section">
                            <div class="note-title">
                                <a href="{{ route('library.show', $note->book->slug) }}">
                                    {{ $note->book->title }}
                                </a>
                            </div>

                            @if($note->page_number)
                                <span class="page-badge">
                                    <i class="fas fa-file-alt"></i> Page {{ $note->page_number }}
                                </span>
                            @endif
                        </div>

                        <div class="note-privacy-section">
                            @if($note->is_private)
                                <i class="fas fa-lock privacy-icon" title="Private note"></i>
                            @else
                                <i class="fas fa-unlock privacy-icon" title="Public note"></i>
                            @endif
                        </div>
                    </div>

                    <div class="note-text-box">
                        {{ $note->note }}
                    </div>

                    <div class="note-meta">
                        <i class="fal fa-clock"></i> Created {{ $note->created_at->diffForHumans() }} ({{ $note->created_at->format('F j, Y \a\t g:i A') }})
                        @if($note->updated_at != $note->created_at)
                            <span class="note-updated">
                                · Updated {{ $note->updated_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
                </div>

                @if($notes->hasPages())
                    <div class="pagination-wrapper">
                        {{ $notes->links() }}
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <i class="fal fa-sticky-note"></i>
                    <h2>No notes yet</h2>
                    <p>Create notes while reading to remember important details.</p>
                    <a href="{{ route('library.index') }}" class="btn">Browse Library</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
