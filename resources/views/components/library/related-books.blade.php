@props(['books', 'title', 'sectionId'])

@if($books->isNotEmpty())
    <div class="related-books-section" id="{{ $sectionId }}">
        <h3 class="related-books-title text-left">{{ $title }}</h3>
        <div class="related-books-table-container">
            <table class="books-table related-books-table">
                <thead>
                    <tr>
                        <th style="width: 80px;"></th>
                        <th>Title/Edition</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $relatedBook)
                        <tr class="related-book-row">
                            <td class="related-book-cover-cell">
                                <img src="{{ $relatedBook->getThumbnailUrl() }}" 
                                     alt="{{ $relatedBook->title }}" 
                                     class="related-book-cover">
                            </td>
                            <td class="related-book-details-cell">
                                <div class="related-book-title">
                                    <a href="{{ route('library.show', $relatedBook->slug) }}"><span>{{ $relatedBook->title }}</span>
                                        @if($relatedBook->subtitle)
                                            &nbsp;&ndash; <span style="font-weight: normal">{{ $relatedBook->subtitle }}</span>
                                        @endif
                                    </a>
                                </div>
                                <div class="related-book-metadata">
                                    {{ $relatedBook->publication_year ?? 'N/A' }}
                                    @if($relatedBook->publisher)
                                        , {{ $relatedBook->publisher->name }}
                                    @endif
                                </div>
                                <div class="related-book-description">
                                    @php
                                        $descriptionParts = [];
                                        if($relatedBook->purposeClassifications->isNotEmpty()) {
                                            $descriptionParts[] = $relatedBook->purposeClassifications->pluck('value')->join(', ');
                                        }
                                        if($relatedBook->learnerLevelClassifications->isNotEmpty()) {
                                            $descriptionParts[] = $relatedBook->learnerLevelClassifications->pluck('value')->join(', ');
                                        }
                                        if($relatedBook->languages->isNotEmpty()) {
                                            $descriptionParts[] = $relatedBook->languages->pluck('name')->join(', ');
                                        }
                                    @endphp
                                    {{ implode(', ', $descriptionParts) }}
                                </div>
                                <div class="related-book-description">
                                    @if($relatedBook->access_level === 'full')
                                        Full access
                                    @elseif($relatedBook->access_level === 'limited')
                                        Limited access
                                    @else
                                        Unavailable
                                    @endif
                                </div>
                            </td>
                            <td class="related-book-actions-cell">
                                <div class="related-book-actions">
                                    <a href="{{ route('library.show', $relatedBook->slug) }}" class="button button-primary btn-view">Locate</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<style>
.related-books-section {
    margin-top: 2rem;
    width: 100%;
}

.related-books-table-container {
    margin-top: 1rem;
}

.related-books-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #333;
}

/* Scoped table styles to avoid conflicts */
.related-books-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.related-books-table thead {
    background-color: #f5f5f5;
    border-bottom: 2px solid #ddd;
}

.related-books-table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    color: #666;
}

.related-book-row {
    border-bottom: 1px solid #e0e0e0;
    transition: background-color 0.2s;
}

.related-book-row:hover {
    background-color: #f9f9f9;
}

.related-book-cover-cell {
    padding: 0.75rem;
    text-align: center;
}

.related-book-cover {
    width: 60px;
    height: 90px;
    object-fit: cover;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.related-book-details-cell {
    padding: 0.75rem 1rem;
    vertical-align: middle;
}

.related-book-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.related-book-title a {
    color: #1d496a;
    text-decoration: none;
}

.related-book-title a:hover {
    text-decoration: underline;
}

.related-book-metadata {
    font-size: 0.875rem;
    color: #666;
    margin-bottom: 0.25rem;
}

.related-book-description {
    font-size: 0.813rem;
    color: #888;
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.related-book-access {
    font-size: 0.813rem;
    color: #888;
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.related-book-actions-cell {
    padding: 0.75rem;
    text-align: center;
    vertical-align: middle;
}

.related-book-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: stretch;
}

@media (max-width: 768px) {
    .related-book-cover {
        width: 50px;
        height: 75px;
    }
    
    .related-book-title {
        font-size: 0.938rem;
    }
    
    .related-book-metadata,
    .related-book-description {
        font-size: 0.75rem;
    }
}
</style>
