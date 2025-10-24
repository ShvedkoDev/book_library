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
                    <a href="{{ asset('storage/' . $pdfFile->file_path) }}" target="_blank" class="book-action-btn btn-primary">Preview PDF</a>
                    <a href="{{ route('library.download', ['book' => $book->id, 'file' => $pdfFile->id]) }}" class="book-action-btn btn-secondary">Download PDF</a>
                @elseif($book->access_level === 'limited')
                    <button class="book-action-btn btn-primary" disabled>Limited Preview</button>
                    <button class="book-action-btn btn-secondary" disabled>Request Full Access</button>
                @else
                    <button class="book-action-btn btn-primary" disabled>Not Available</button>
                    <button class="book-action-btn btn-secondary">Request Access</button>
                @endif
            </div>

            <div class="book-rating">
                <div class="stars">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="star {{ $i <= 4 ? '' : 'empty' }}">★</span>
                    @endfor
                </div>
                <div class="rating-text">{{ $book->view_count }} views</div>
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

                @if($book->keywords)
                    <div style="margin-top: 1.5rem;">
                        <strong>Keywords:</strong>
                        @foreach(explode(',', $book->keywords) as $keyword)
                            <span style="display: inline-block; background: #e9ecef; padding: 0.25rem 0.75rem; margin: 0.25rem; border-radius: 1rem; font-size: 0.875rem;">
                                {{ trim($keyword) }}
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
</script>
@endpush
