@props(['books', 'title', 'sectionId'])

@if($books->isNotEmpty())
    <div class="related-books" id="{{ $sectionId }}">
        <h3 class="section-title text-left">{{ $title }}</h3>
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
                        <tr class="book-row">
                            <td class="book-cover-cell">
                                <img src="{{ $relatedBook->getThumbnailUrl() }}" 
                                     alt="{{ $relatedBook->title }}" 
                                     class="book-cover">
                            </td>
                            <td class="book-details-cell">
                                <div class="book-title">
                                    <a href="{{ route('library.show', $relatedBook->slug) }}">
                                        <span>{{ $relatedBook->title }}</span>
                                        @if($relatedBook->subtitle)
                                            &nbsp;&ndash; <span style="font-weight: normal">{{ $relatedBook->subtitle }}</span>
                                        @endif
                                    </a>
                                </div>
                                <div class="book-metadata">
                                    {{ $relatedBook->publication_year ?? 'N/A' }}
                                    @if($relatedBook->publisher)
                                        , {{ $relatedBook->publisher->name }}
                                    @endif
                                </div>
                                <div class="book-description">
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
                                <div class="book-description">
                                    @if($relatedBook->access_level === 'full')
                                        Full access
                                    @elseif($relatedBook->access_level === 'limited')
                                        Limited access
                                    @else
                                        Unavailable
                                    @endif
                                </div>
                            </td>
                            <td class="book-actions-cell">
                                <div class="book-actions">
                                    <a href="{{ route('library.show', $relatedBook->slug) }}" class="book-action-btn view-btn">
                                        <i class="fal fa-book-open"></i> View
                                    </a>
                                    @if($relatedBook->access_level === 'full' && $relatedBook->pdf_path)
                                        <a href="{{ route('library.pdf.viewer', $relatedBook->slug) }}" class="book-action-btn pdf-btn">
                                            <i class="fal fa-file-pdf"></i> Read
                                        </a>
                                    @endif
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
.related-books {
    margin-top: 2rem;
    width: 100%;
}

.related-books-table-container {
    margin-top: 1rem;
}

.related-books .section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #333;
}

/* Reuse the library table styles */
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

.related-books-table .book-row {
    border-bottom: 1px solid #e0e0e0;
    transition: background-color 0.2s;
}

.related-books-table .book-row:hover {
    background-color: #f9f9f9;
}

.related-books-table .book-cover-cell {
    padding: 0.75rem;
    text-align: center;
}

.related-books-table .book-cover {
    width: 60px;
    height: 90px;
    object-fit: cover;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.related-books-table .book-details-cell {
    padding: 0.75rem 1rem;
    vertical-align: middle;
}

.related-books-table .book-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.related-books-table .book-title a {
    color: #1d496a;
    text-decoration: none;
}

.related-books-table .book-title a:hover {
    text-decoration: underline;
}

.related-books-table .book-metadata {
    font-size: 0.875rem;
    color: #666;
    margin-bottom: 0.25rem;
}

.related-books-table .book-description {
    font-size: 0.813rem;
    color: #888;
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.related-books-table .book-actions-cell {
    padding: 0.75rem;
    text-align: center;
    vertical-align: middle;
}

.related-books-table .book-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: stretch;
}

.related-books-table .book-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
}

.related-books-table .view-btn {
    background-color: #1d496a;
    color: white;
}

.related-books-table .view-btn:hover {
    background-color: #005a8a;
}

.related-books-table .pdf-btn {
    background-color: #d32f2f;
    color: white;
}

.related-books-table .pdf-btn:hover {
    background-color: #b71c1c;
}

@media (max-width: 768px) {
    .related-books-table .book-cover {
        width: 50px;
        height: 75px;
    }
    
    .related-books-table .book-title {
        font-size: 0.938rem;
    }
    
    .related-books-table .book-metadata,
    .related-books-table .book-description {
        font-size: 0.75rem;
    }
    
    .related-books-table .book-action-btn {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
}
</style>
