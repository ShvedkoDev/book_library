@extends('layouts.library')

@section('title', 'Library - Micronesian Teachers Digital Library')
@section('description', 'Browse our collection of over 2,000 educational resources in local Micronesian languages')

@section('content')
<div class="container-fluid library-container">
    <div class="library-layout">
        <!-- Sidebar with Filters -->
        <aside class="library-sidebar">
            <div class="sidebar-content">
                <!-- Search Box -->
                <div class="search-box">
                    <form action="{{ route('library.index') }}" method="GET" id="library-search-form">
                        <div class="search-input-wrapper">
                            <input
                                type="text"
                                name="search"
                                id="search-input"
                                placeholder="Search books..."
                                value="{{ $search ?? '' }}"
                                aria-label="Search books"
                            >
                            <button type="submit" class="search-btn" aria-label="Search">
                                <i class="fal fa-search"></i>
                            </button>
                        </div>

                        <!-- Hidden fields to preserve filters and sorting -->
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
                    </form>
                </div>

                <!-- Filters -->
                <div class="filters-section">
                    <h3 class="filters-title">Filters</h3>

                    <form action="{{ route('library.index') }}" method="GET" id="filters-form">
                        <!-- Preserve search query -->
                        @if($search)
                            <input type="hidden" name="search" value="{{ $search }}">
                        @endif
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">

                        <!-- Subject Filter -->
                        @if($availableSubjects->isNotEmpty())
                            @foreach($availableSubjects as $subjectType)
                                @if($subjectType->classificationValues->isNotEmpty())
                                    <div class="filter-group">
                                        <h4 class="filter-toggle" onclick="toggleFilterGroup(this)">
                                            <i class="fal fa-chevron-right toggle-icon"></i>
                                            {{ $subjectType->name }}
                                        </h4>
                                        <div class="checkbox-group collapsed">
                                            @foreach($subjectType->classificationValues as $classification)
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="subjects[]"
                                                        value="{{ $classification->id }}"
                                                        {{ in_array($classification->id, $filters['subjects'] ?? []) ? 'checked' : '' }}
                                                        onchange="document.getElementById('filters-form').submit()"
                                                    >
                                                    &nbsp;{{ $classification->value }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <!-- Grade Level Filter -->
                        @if($availableGrades->isNotEmpty())
                            @foreach($availableGrades as $gradeType)
                                @if($gradeType->classificationValues->isNotEmpty())
                                    <div class="filter-group">
                                        <h4 class="filter-toggle" onclick="toggleFilterGroup(this)">
                                            <i class="fal fa-chevron-right toggle-icon"></i>
                                            {{ $gradeType->name }}
                                        </h4>
                                        <div class="checkbox-group collapsed">
                                            @foreach($gradeType->classificationValues as $classification)
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="grades[]"
                                                        value="{{ $classification->id }}"
                                                        {{ in_array($classification->id, $filters['grades'] ?? []) ? 'checked' : '' }}
                                                        onchange="document.getElementById('filters-form').submit()"
                                                    >
                                                    &nbsp;{{ $classification->value }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <!-- Resource Type Filter -->
                        @if($availableTypes->isNotEmpty())
                            @foreach($availableTypes as $typeGroup)
                                @if($typeGroup->classificationValues->isNotEmpty())
                                    <div class="filter-group">
                                        <h4 class="filter-toggle" onclick="toggleFilterGroup(this)">
                                            <i class="fal fa-chevron-right toggle-icon"></i>
                                            {{ $typeGroup->name }}
                                        </h4>
                                        <div class="checkbox-group collapsed">
                                            @foreach($typeGroup->classificationValues as $classification)
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="types[]"
                                                        value="{{ $classification->id }}"
                                                        {{ in_array($classification->id, $filters['types'] ?? []) ? 'checked' : '' }}
                                                        onchange="document.getElementById('filters-form').submit()"
                                                    >
                                                    &nbsp;{{ $classification->value }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <!-- Language Filter -->
                        @if($availableLanguages->isNotEmpty())
                            <div class="filter-group">
                                <h4 class="filter-toggle" onclick="toggleFilterGroup(this)">
                                    <i class="fal fa-chevron-right toggle-icon"></i>
                                    Language
                                </h4>
                                <div class="checkbox-group collapsed">
                                    @foreach($availableLanguages as $language)
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="languages[]"
                                                value="{{ $language->code }}"
                                                {{ in_array($language->code, $filters['languages'] ?? []) ? 'checked' : '' }}
                                                onchange="document.getElementById('filters-form').submit()"
                                            >
                                            &nbsp;{{ $language->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Clear Filters Button -->
                        @if(!empty(array_filter($filters)))
                            <div class="filter-actions">
                                <a href="{{ route('library.index') }}" class="button button-secondary">Clear All Filters</a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main_content library-content">
            <!-- Library Header -->
            <div class="library-header">
                <div class="results-info">
                    <h3>Available resources</h3>
                    <p id="results-count">Showing {{ $books->count() }} of {{ $books->total() }} books and other resources</p>
                </div>
                <div class="sort-options">
                    <label for="sort-select">Sort by:&nbsp;</label>
                    <select id="sort-select" onchange="changeSorting(this.value)">
                        <option value="title-asc" {{ $sortBy == 'title' && $sortDirection == 'asc' ? 'selected' : '' }}>Title A-Z</option>
                        <option value="title-desc" {{ $sortBy == 'title' && $sortDirection == 'desc' ? 'selected' : '' }}>Title Z-A</option>
                        <option value="publication_year-desc" {{ $sortBy == 'publication_year' && $sortDirection == 'desc' ? 'selected' : '' }}>Newest</option>
                        <option value="publication_year-asc" {{ $sortBy == 'publication_year' && $sortDirection == 'asc' ? 'selected' : '' }}>Oldest</option>
                        <option value="view_count-desc" {{ $sortBy == 'view_count' && $sortDirection == 'desc' ? 'selected' : '' }}>Most Popular</option>
                    </select>
                </div>
            </div>

            <!-- Books Table -->
            <table class="books-table" id="booksTable">
                <thead>
                    <tr>
                        <th style="width: 80px;"></th>
                        <th>Title/Edition</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="booksTableBody">
                    @forelse($books as $book)
                        <tr class="book-row">
                            <td class="book-cover-cell">
                                @php
                                    $thumbnail = $book->files->where('file_type', 'thumbnail')->where('is_primary', true)->first();
                                @endphp
                                @if($thumbnail && $thumbnail->file_path)
                                    <img src="{{ asset('storage/' . $thumbnail->file_path) }}"
                                         alt="{{ $book->title }}"
                                         class="book-cover">
                                @else
                                    <img src="https://via.placeholder.com/60x80?text=No+Cover"
                                         alt="{{ $book->title }}"
                                         class="book-cover">
                                @endif
                            </td>
                            <td class="book-details-cell">
                                <div class="book-title">
                                    <a href="{{ route('library.show', $book->slug) }}">{{ $book->title }}</a>
                                </div>
                                <div class="book-metadata">
                                    {{ $book->publication_year ?? 'N/A' }}
                                    @if($book->publisher)
                                        , {{ $book->publisher->name }}
                                    @endif
                                </div>
                                <div class="book-description">
                                    @if($book->purposeClassifications->isNotEmpty())
                                        {{ $book->purposeClassifications->pluck('value')->join(', ') }},
                                    @endif
                                    @if($book->learnerLevelClassifications->isNotEmpty())
                                        {{ $book->learnerLevelClassifications->pluck('value')->join(', ') }},
                                    @endif
                                    @if($book->languages->isNotEmpty())
                                        {{ $book->languages->pluck('name')->join(', ') }},
                                    @endif
                                    {{ ucfirst($book->access_level) }} Access
                                </div>
                            </td>
                            <td class="book-actions-cell">
                                <div class="book-actions">
                                    <a href="{{ route('library.show', $book->slug) }}" class="button button-primary btn-view">Locate</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 40px;">
                                <p>No books found matching your criteria.</p>
                                <a href="{{ route('library.index') }}" class="button button-secondary">Clear Filters</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($books->hasPages())
                <div class="pagination-container">
                    <div class="entries-info">
                        <label for="per-page-select">Show&nbsp;</label>
                        <select id="per-page-select" onchange="changePerPage(this.value)">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span>&nbsp;entries</span>
                    </div>
                    <div class="pagination-controls">
                        @if($books->onFirstPage())
                            <button class="pagination-btn nav-arrow" disabled>←</button>
                        @else
                            <a href="{{ $books->previousPageUrl() }}" class="pagination-btn nav-arrow">←</a>
                        @endif

                        @foreach($books->getUrlRange(1, $books->lastPage()) as $page => $url)
                            @if($page == $books->currentPage())
                                <button class="pagination-btn active">{{ $page }}</button>
                            @else
                                <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($books->hasMorePages())
                            <a href="{{ $books->nextPageUrl() }}" class="pagination-btn nav-arrow">→</a>
                        @else
                            <button class="pagination-btn nav-arrow" disabled>→</button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleFilterGroup(element) {
        const icon = element.querySelector('.toggle-icon');
        const group = element.nextElementSibling;

        group.classList.toggle('collapsed');
        icon.classList.toggle('fa-chevron-right');
        icon.classList.toggle('fa-chevron-down');
    }

    function changeSorting(value) {
        const [sortBy, sortDirection] = value.split('-');
        const url = new URL(window.location.href);
        url.searchParams.set('sort_by', sortBy);
        url.searchParams.set('sort_direction', sortDirection);
        window.location.href = url.toString();
    }

    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.delete('page'); // Reset to page 1
        window.location.href = url.toString();
    }
</script>
@endpush
