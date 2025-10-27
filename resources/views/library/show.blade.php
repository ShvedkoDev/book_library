@extends('layouts.library')

@section('title', $book->title . ' - Micronesian Teachers Digital Library')
@section('description', Str::limit($book->description ?? 'Educational resource for Micronesian teachers', 160))
@section('og_type', 'book')

@push('styles')
<style>
    .book-page-container {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2rem;
        padding: 2rem 0;
    }

    .book-cover-section {
        position: sticky;
        top: 2rem;
        height: fit-content;
    }

    .book-cover-section .book-cover {
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin-bottom: 1rem;
    }

    .access-status {
        padding: 0.5rem;
        border-radius: 6px;
        text-align: center;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .access-status.full-access {
        background-color: #d4edda;
        color: #155724;
    }

    .access-status.limited-access {
        background-color: #fff3cd;
        color: #856404;
    }

    .access-status.unavailable {
        background-color: #f8d7da;
        color: #721c24;
    }

    .book-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .book-action-btn {
        padding: 0.75rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }

    .book-action-btn.btn-primary {
        background-color: #007cba;
        color: white;
    }

    .book-action-btn.btn-secondary {
        background-color: #f0f0f0;
        color: #333;
    }

    .book-rating {
        border-top: 1px solid #e0e0e0;
        padding-top: 1rem;
    }

    .stars {
        color: #ffc107;
        font-size: 1.2rem;
    }

    .stars .empty {
        color: #ddd;
    }

    .rating-text {
        font-size: 0.875rem;
        color: #666;
        margin: 0.5rem 0;
    }

    .user-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .user-action {
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .user-action:hover {
        background: #e9ecef;
    }

    .book-info-section {
        max-width: 900px;
    }

    .book-header {
        margin-bottom: 2rem;
    }

    .collection-link {
        color: #007cba;
        text-decoration: none;
        font-size: 0.875rem;
    }

    .book-title {
        font-size: 2.5rem;
        margin: 0.5rem 0;
        color: #333;
    }

    .book-subtitle {
        font-size: 1.5rem;
        color: #666;
        font-weight: 400;
        margin: 0.5rem 0;
    }

    .book-author {
        font-size: 1.1rem;
        color: #444;
        margin: 1rem 0;
    }

    .book-meta {
        color: #666;
        font-size: 0.875rem;
    }

    .book-meta span {
        margin-right: 0.5rem;
    }

    .book-description {
        margin: 2rem 0;
        line-height: 1.6;
    }

    .book-nav-tabs {
        border-bottom: 2px solid #e0e0e0;
        margin: 2rem 0;
    }

    .book-nav-tab {
        background: none;
        border: none;
        padding: 1rem 1.5rem;
        cursor: pointer;
        font-weight: 600;
        color: #666;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.3s;
    }

    .book-nav-tab.active {
        color: #007cba;
        border-bottom-color: #007cba;
    }

    .book-content-section {
        display: none;
        padding: 2rem 0;
    }

    .book-content-section.active {
        display: block;
    }

    .book-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .detail-group h3 {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        color: #333;
    }

    .detail-item {
        display: flex;
        margin-bottom: 0.75rem;
    }

    .detail-label {
        font-weight: 600;
        min-width: 140px;
        color: #555;
    }

    .detail-value {
        color: #333;
    }

    .related-books {
        margin-top: 3rem;
    }

    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .book-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        transition: box-shadow 0.3s;
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
    }

    .book-card-author {
        font-size: 0.875rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .book-card-meta {
        font-size: 0.8rem;
        color: #999;
        margin-bottom: 0.75rem;
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
    }

    @media (max-width: 768px) {
        .book-page-container {
            grid-template-columns: 1fr;
        }

        .book-cover-section {
            position: static;
        }
    }
</style>
@endpush

@section('content')
<div class="container library-book-detail">
    <!-- Success Messages -->
    @if(session('success'))
        <div style="padding: 1rem; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 6px; margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="padding: 1rem; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 6px; margin-bottom: 1rem;">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="book-page-container">
        <!-- Book Cover Section -->
        <div class="book-cover-section">
            @php
                $pdfFile = $book->files->where('file_type', 'pdf')->where('is_primary', true)->first();
            @endphp

            <img src="{{ $book->getThumbnailUrl() }}" alt="{{ $book->title }}" class="book-cover">

            <div class="access-status {{ $book->access_level === 'full' ? 'full-access' : ($book->access_level === 'limited' ? 'limited-access' : 'unavailable') }}">
                <span>
                    @if($book->access_level === 'full')
                        📖 Full Access
                    @elseif($book->access_level === 'limited')
                        📄 Limited Access
                    @else
                        🔒 Unavailable
                    @endif
                </span>
            </div>

            <div class="book-actions">
                @if($book->access_level === 'full' && $pdfFile)
                    <a href="{{ route('library.view-pdf', ['book' => $book->id, 'file' => $pdfFile->id]) }}" target="_blank" class="book-action-btn btn-primary">View PDF</a>
                    <a href="{{ route('library.download', ['book' => $book->id, 'file' => $pdfFile->id]) }}" class="book-action-btn btn-secondary">Download PDF</a>
                @elseif($book->access_level === 'limited' && $pdfFile)
                    <a href="{{ route('library.view-pdf', ['book' => $book->id, 'file' => $pdfFile->id]) }}" target="_blank" class="book-action-btn btn-primary">Limited Preview</a>
                    <button class="book-action-btn btn-secondary" disabled>Request Full Access</button>
                @else
                    <button class="book-action-btn btn-primary" disabled>Not Available</button>
                    @auth
                        <button onclick="openAccessRequestModal()" class="book-action-btn btn-secondary">
                            Request Access
                        </button>
                    @else
                        <a href="{{ route('login') }}"
                           class="book-action-btn btn-secondary"
                           style="text-decoration: none; text-align: center;"
                           title="Please log in to request access">
                            Login to Request Access
                        </a>
                    @endauth
                @endif
            </div>

            <div class="book-rating">
                <div class="stars">
                    @php
                        $roundedRating = round($averageRating);
                    @endphp
                    @for($i = 1; $i <= 5; $i++)
                        <span class="star {{ $i <= $roundedRating ? '' : 'empty' }}">★</span>
                    @endfor
                </div>
                <div class="rating-text">
                    @if($totalRatings > 0)
                        {{ number_format($averageRating, 1) }} average ({{ $totalRatings }} {{ Str::plural('rating', $totalRatings) }})
                    @else
                        No ratings yet
                    @endif
                </div>
                <div class="rating-text" style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid #e0e0e0;">
                    {{ number_format($book->view_count) }} {{ Str::plural('view', $book->view_count) }}
                </div>
            </div>
        </div>

        <!-- Book Info Section -->
        <div class="book-info-section">
            <div class="book-header">
                @if($book->collection)
                    <a href="{{ route('library.index', ['collection' => $book->collection->slug]) }}" class="collection-link">
                        {{ $book->collection->title }}
                    </a>
                @endif
                <h1 class="book-title">{{ $book->title }}</h1>
                @if($book->subtitle)
                    <h2 class="book-subtitle">{{ $book->subtitle }}</h2>
                @endif
                <div class="book-author">
                    @if($book->creators->isNotEmpty())
                        by {{ $book->creators->pluck('name')->join(', ') }}
                    @endif
                </div>
                <div class="book-meta">
                    @if($book->publication_year)
                        <span>Published {{ $book->publication_year }}</span>
                        <span>•</span>
                    @endif
                    @if($book->pages)
                        <span>{{ $book->pages }} pages</span>
                        <span>•</span>
                    @endif
                    @if($book->languages->isNotEmpty())
                        <span>{{ $book->languages->pluck('name')->join(', ') }}</span>
                    @endif
                </div>
            </div>

            @if($book->description)
                <div class="book-description">
                    <p>{{ $book->description }}</p>
                </div>
            @endif

            <!-- Content Navigation -->
            <nav class="book-nav-tabs">
                <button class="book-nav-tab active" onclick="showTab('overview')">Overview</button>
                <button class="book-nav-tab" onclick="showTab('details')">Details</button>
                <button class="book-nav-tab" onclick="showTab('library')">Library Locations</button>
            </nav>

            <!-- Overview Tab -->
            <div class="book-content-section active" id="overview">
                @if($book->abstract)
                    <p>{{ $book->abstract }}</p>
                @endif

                @if($book->table_of_contents)
                    <h3>Table of Contents</h3>
                    <div>{!! nl2br(e($book->table_of_contents)) !!}</div>
                @endif

                @if($book->keywords && $book->keywords->isNotEmpty())
                    <div style="margin-top: 1.5rem;">
                        <strong>Keywords:</strong>
                        @foreach($book->keywords as $keywordObj)
                            <span style="display: inline-block; background: #e9ecef; padding: 0.25rem 0.75rem; margin: 0.25rem; border-radius: 1rem; font-size: 0.875rem;">
                                {{ $keywordObj->keyword }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Details Tab -->
            <div class="book-content-section" id="details">
                <div class="book-details-grid">
                    <div class="detail-group">
                        <h3>Contributors</h3>
                        @if($book->creators->isNotEmpty())
                            @foreach($book->creators as $creator)
                                <div class="detail-item">
                                    <span class="detail-label">{{ $creator->pivot->role ?? 'Author' }}:</span>
                                    <span class="detail-value">{{ $creator->name }}</span>
                                </div>
                            @endforeach
                        @endif
                        @if($book->publisher)
                            <div class="detail-item">
                                <span class="detail-label">Publisher:</span>
                                <span class="detail-value">{{ $book->publisher->name }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="detail-group">
                        <h3>Classifications</h3>
                        @if($book->purposeClassifications->isNotEmpty())
                            <div class="detail-item">
                                <span class="detail-label">Subject:</span>
                                <span class="detail-value">{{ $book->purposeClassifications->pluck('value')->join(', ') }}</span>
                            </div>
                        @endif
                        @if($book->learnerLevelClassifications->isNotEmpty())
                            <div class="detail-item">
                                <span class="detail-label">Grade Level:</span>
                                <span class="detail-value">{{ $book->learnerLevelClassifications->pluck('value')->join(', ') }}</span>
                            </div>
                        @endif
                        @if($book->languages->isNotEmpty())
                            <div class="detail-item">
                                <span class="detail-label">Language:</span>
                                <span class="detail-value">{{ $book->languages->pluck('name')->join(', ') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="detail-group">
                        <h3>Publication Details</h3>
                        <div class="detail-item">
                            <span class="detail-label">Year:</span>
                            <span class="detail-value">{{ $book->publication_year ?? 'N/A' }}</span>
                        </div>
                        @if($book->pages)
                            <div class="detail-item">
                                <span class="detail-label">Pages:</span>
                                <span class="detail-value">{{ $book->pages }}</span>
                            </div>
                        @endif
                        @if($book->physical_type)
                            <div class="detail-item">
                                <span class="detail-label">Type:</span>
                                <span class="detail-value">{{ $book->physical_type }}</span>
                            </div>
                        @endif
                    </div>

                    @if($book->isbn_10 || $book->isbn_13 || $book->palm_code)
                        <div class="detail-group">
                            <h3>Identifiers</h3>
                            @if($book->isbn_10)
                                <div class="detail-item">
                                    <span class="detail-label">ISBN-10:</span>
                                    <span class="detail-value">{{ $book->isbn_10 }}</span>
                                </div>
                            @endif
                            @if($book->isbn_13)
                                <div class="detail-item">
                                    <span class="detail-label">ISBN-13:</span>
                                    <span class="detail-value">{{ $book->isbn_13 }}</span>
                                </div>
                            @endif
                            @if($book->palm_code)
                                <div class="detail-item">
                                    <span class="detail-label">PALM Code:</span>
                                    <span class="detail-value">{{ $book->palm_code }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Library Locations Tab -->
            <div class="book-content-section" id="library">
                @if($book->libraryReferences->isNotEmpty())
                    @foreach($book->libraryReferences as $reference)
                        <div class="detail-group" style="margin-bottom: 1.5rem;">
                            <h3>{{ $reference->library_name }}</h3>
                            @if($reference->reference_number)
                                <div class="detail-item">
                                    <span class="detail-label">Reference Number:</span>
                                    <span class="detail-value">{{ $reference->reference_number }}</span>
                                </div>
                            @endif
                            @if($reference->call_number)
                                <div class="detail-item">
                                    <span class="detail-label">Call Number:</span>
                                    <span class="detail-value">{{ $reference->call_number }}</span>
                                </div>
                            @endif
                            @if($reference->catalog_link)
                                <div class="detail-item">
                                    <span class="detail-label">Catalog:</span>
                                    <span class="detail-value">
                                        <a href="{{ $reference->catalog_link }}" target="_blank">View in Library Catalog</a>
                                    </span>
                                </div>
                            @endif
                            @if($reference->notes)
                                <div class="detail-item">
                                    <span class="detail-label">Notes:</span>
                                    <span class="detail-value">{{ $reference->notes }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p>No physical library references available for this book.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Related Books Sections -->
    @if($relatedByCollection->isNotEmpty())
        <div class="related-books">
            <h2>More books from the same collection</h2>
            <div class="books-grid">
                @foreach($relatedByCollection->take(6) as $relatedBook)
                    <div class="book-card">
                        <img src="{{ $relatedBook->getThumbnailUrl() }}" alt="{{ $relatedBook->title }}" class="book-card-cover">
                        <div class="book-card-title">{{ Str::limit($relatedBook->title, 50) }}</div>
                        <div class="book-card-author">{{ $relatedBook->creators->pluck('name')->join(', ') }}</div>
                        <div class="book-card-meta">{{ $relatedBook->publication_year }}</div>
                        <a href="{{ route('library.show', $relatedBook->slug) }}" class="book-card-btn">View</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($relatedByLanguage->isNotEmpty())
        <div class="related-books">
            <h2>More books in the same language</h2>
            <div class="books-grid">
                @foreach($relatedByLanguage->take(6) as $relatedBook)
                    <div class="book-card">
                        <img src="{{ $relatedBook->getThumbnailUrl() }}" alt="{{ $relatedBook->title }}" class="book-card-cover">
                        <div class="book-card-title">{{ Str::limit($relatedBook->title, 50) }}</div>
                        <div class="book-card-author">{{ $relatedBook->creators->pluck('name')->join(', ') }}</div>
                        <div class="book-card-meta">{{ $relatedBook->publication_year }}</div>
                        <a href="{{ route('library.show', $relatedBook->slug) }}" class="book-card-btn">View</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($relatedByCreator->isNotEmpty())
        <div class="related-books">
            <h2>More books by the same author</h2>
            <div class="books-grid">
                @foreach($relatedByCreator->take(6) as $relatedBook)
                    <div class="book-card">
                        <img src="{{ $relatedBook->getThumbnailUrl() }}" alt="{{ $relatedBook->title }}" class="book-card-cover">
                        <div class="book-card-title">{{ Str::limit($relatedBook->title, 50) }}</div>
                        <div class="book-card-author">{{ $relatedBook->creators->pluck('name')->join(', ') }}</div>
                        <div class="book-card-meta">{{ $relatedBook->publication_year }}</div>
                        <a href="{{ route('library.show', $relatedBook->slug) }}" class="book-card-btn">View</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Reviews and Ratings Section -->
    <div class="reviews-section" style="margin-top: 3rem; padding: 2rem; background: #f9f9f9; border-radius: 8px;">
        <h2 style="margin-bottom: 1.5rem; color: #333;">Reviews & Ratings</h2>

        <!-- Rating Histogram -->
        <div class="rating-histogram" style="margin-bottom: 2rem; padding: 1.5rem; background: white; border-radius: 8px;">
            <h3 style="font-size: 1.2rem; margin-bottom: 1rem; color: #555;">Rating Distribution</h3>
            @if($totalRatings > 0)
                <div style="display: flex; gap: 2rem; align-items: center;">
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; font-weight: bold; color: #007cba;">{{ number_format($averageRating, 1) }}</div>
                        <div class="stars" style="font-size: 1.5rem;">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star {{ $i <= round($averageRating) ? '' : 'empty' }}">★</span>
                            @endfor
                        </div>
                        <div style="color: #666; margin-top: 0.5rem;">{{ $totalRatings }} {{ Str::plural('rating', $totalRatings) }}</div>
                    </div>
                    <div style="flex: 1;">
                        @foreach([5, 4, 3, 2, 1] as $rating)
                            @php
                                $count = $ratingDistribution[$rating];
                                $percentage = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0;
                            @endphp
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                <span style="min-width: 60px; color: #666;">{{ $rating }} stars</span>
                                <div style="flex: 1; height: 20px; background: #e0e0e0; border-radius: 10px; overflow: hidden;">
                                    <div style="height: 100%; background: #ffc107; width: {{ $percentage }}%; transition: width 0.3s;"></div>
                                </div>
                                <span style="min-width: 60px; text-align: right; color: #666;">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p style="color: #666; text-align: center; padding: 2rem;">No ratings yet. Be the first to rate this book!</p>
            @endif
        </div>

        <!-- User Rating Form -->
        @auth
            <div class="user-rating-form" style="margin-bottom: 2rem; padding: 1.5rem; background: white; border-radius: 8px;">
                <h3 style="font-size: 1.2rem; margin-bottom: 1rem; color: #555;">Rate this book</h3>
                <form action="{{ route('library.rate', $book->id) }}" method="POST" style="display: flex; align-items: center; gap: 1rem;">
                    @csrf
                    <div class="star-rating" style="display: flex; gap: 0.5rem;">
                        @for($i = 1; $i <= 5; $i++)
                            <label style="cursor: pointer; font-size: 2rem;">
                                <input type="radio" name="rating" value="{{ $i }}" style="display: none;"
                                    {{ $userRating && $userRating->rating == $i ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                <span class="rating-star" data-rating="{{ $i }}"
                                    style="color: {{ $userRating && $i <= $userRating->rating ? '#ffc107' : '#ddd' }}; transition: color 0.2s;">★</span>
                            </label>
                        @endfor
                    </div>
                    @if($userRating)
                        <span style="color: #666;">Your rating: {{ $userRating->rating }}/5</span>
                    @else
                        <span style="color: #666;">Click to rate</span>
                    @endif
                </form>
            </div>
        @else
            <div style="margin-bottom: 2rem; padding: 1.5rem; background: white; border-radius: 8px; text-align: center;">
                <p style="color: #666; margin-bottom: 1rem;">Please <a href="{{ route('login') }}" style="color: #007cba; text-decoration: underline;">log in</a> to rate this book.</p>
            </div>
        @endauth

        <!-- User Review Form -->
        @auth
            <div class="user-review-form" style="margin-bottom: 2rem; padding: 1.5rem; background: white; border-radius: 8px;">
                <h3 style="font-size: 1.2rem; margin-bottom: 1rem; color: #555;">Write a review</h3>
                <form action="{{ route('library.review', $book->id) }}" method="POST">
                    @csrf
                    <textarea name="review" rows="5" placeholder="Share your thoughts about this book..."
                        style="width: 100%; padding: 1rem; border: 1px solid #ddd; border-radius: 6px; resize: vertical; font-family: inherit;"
                        required minlength="10" maxlength="2000"></textarea>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                        <span style="color: #999; font-size: 0.875rem;">Reviews are moderated and will appear after approval.</span>
                        <button type="submit" style="padding: 0.75rem 1.5rem; background: #007cba; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                            Submit Review
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div style="margin-bottom: 2rem; padding: 1.5rem; background: white; border-radius: 8px; text-align: center;">
                <p style="color: #666;">Please <a href="{{ route('login') }}" style="color: #007cba; text-decoration: underline;">log in</a> to write a review.</p>
            </div>
        @endauth

        <!-- Existing Reviews -->
        <div class="existing-reviews">
            <h3 style="font-size: 1.2rem; margin-bottom: 1rem; color: #555;">User Reviews ({{ $book->reviews->count() }})</h3>
            @forelse($book->reviews as $review)
                <div style="padding: 1.5rem; background: white; border-radius: 8px; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                        <div>
                            <strong style="color: #333;">{{ $review->user->name }}</strong>
                            @php
                                $reviewUserRating = $book->ratings()->where('user_id', $review->user_id)->first();
                            @endphp
                            @if($reviewUserRating)
                                <div class="stars" style="display: inline-block; margin-left: 1rem; font-size: 1rem;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="star {{ $i <= $reviewUserRating->rating ? '' : 'empty' }}">★</span>
                                    @endfor
                                </div>
                            @endif
                        </div>
                        <span style="color: #999; font-size: 0.875rem;">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    <p style="color: #555; line-height: 1.6; margin: 0;">{{ $review->review }}</p>
                </div>
            @empty
                <div style="padding: 2rem; background: white; border-radius: 8px; text-align: center;">
                    <p style="color: #999;">No reviews yet. Be the first to review this book!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Access Request Modal -->
    @auth
    <div id="accessRequestModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 8px; padding: 2rem; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; color: #333;">Request Access</h2>
                <button onclick="closeAccessRequestModal()" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #999;">&times;</button>
            </div>

            <p style="color: #666; margin-bottom: 1.5rem;">
                Fill out the form below to request access to <strong>{{ $book->title }}</strong>. We will review your request and contact you via email.
            </p>

            <form action="{{ route('library.request-access', $book->id) }}" method="POST">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label for="access_request_name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Name *</label>
                    <input type="text"
                           id="access_request_name"
                           name="name"
                           value="{{ auth()->user()->name }}"
                           required
                           style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; font-family: inherit;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label for="access_request_email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Email *</label>
                    <input type="email"
                           id="access_request_email"
                           name="email"
                           value="{{ auth()->user()->email }}"
                           required
                           style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; font-family: inherit;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="access_request_message" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Message (Optional)</label>
                    <textarea id="access_request_message"
                              name="message"
                              rows="4"
                              placeholder="Why do you need access to this book?"
                              style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; resize: vertical; font-family: inherit;"></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button"
                            onclick="closeAccessRequestModal()"
                            style="padding: 0.75rem 1.5rem; background: #f0f0f0; color: #333; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding: 0.75rem 1.5rem; background: #007cba; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endauth
</div>
@endsection

@push('scripts')
<script>
    function showTab(tabId) {
        // Hide all content sections
        document.querySelectorAll('.book-content-section').forEach(section => {
            section.classList.remove('active');
        });

        // Remove active class from all tabs
        document.querySelectorAll('.book-nav-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected content section
        document.getElementById(tabId).classList.add('active');

        // Add active class to clicked tab
        event.target.classList.add('active');
    }

    // Star rating hover effect
    document.addEventListener('DOMContentLoaded', function() {
        const starRating = document.querySelector('.star-rating');
        if (starRating) {
            const stars = starRating.querySelectorAll('.rating-star');
            let currentRating = {{ $userRating ? $userRating->rating : 0 }};

            stars.forEach((star, index) => {
                star.addEventListener('mouseenter', function() {
                    highlightStars(index + 1);
                });

                star.addEventListener('mouseleave', function() {
                    highlightStars(currentRating);
                });
            });

            function highlightStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.style.color = '#ffc107';
                    } else {
                        star.style.color = '#ddd';
                    }
                });
            }
        }
    });

    // Access Request Modal functions
    function openAccessRequestModal() {
        const modal = document.getElementById('accessRequestModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeAccessRequestModal() {
        const modal = document.getElementById('accessRequestModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Close modal when clicking outside
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('accessRequestModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeAccessRequestModal();
                }
            });
        }
    });
</script>
@endpush
