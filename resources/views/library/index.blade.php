@extends('layouts.library')

@section('title', 'Library - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('description', 'Browse our collection of over 2,000 educational resources in local Micronesian languages')

@section('content')

<div class="content print" role="main">
    <div class="title_banner with_sidebar">
        <div class="header-blurb container">
            <div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
                <!-- Breadcrumb NavXT 7.4.1 -->
                <span property="itemListElement" typeof="ListItem">
                    <span class="main-home" property="name">National Vernacular Language Arts (VLA) curriculum</span>
                    <meta property="position" content="1">
                </span>
            </div>
            <div class="handbook-header-menu">
                <h1>Resource library</h1>
            </div>
        </div>
        <aside class="sidebar header-image handbook-image">
            <div class="logo-cell ndoe-cell">
                <img class="ndoe-logo" src="{{ asset('library-assets/images/NDOE.png') }}" alt="Department of Education - National Government">
            </div>
            <div class="right-logos">
                <div class="logo-cell irei-cell">
                    <img class="irei-logo" src="{{ asset('library-assets/images/iREi-top.png') }}" alt="Island Research & Education Initiative">
                </div>
                <div class="logo-cell c4gts-cell">
                    <img class="c4gts-logo" src="{{ asset('library-assets/images/C4GTS.png') }}" alt="Center for Getting Things Started">
                </div>
            </div>
        </aside>
    </div>
    <div class="page-content">
        <div class="container with_sidebar">
            <!-- Sidebar with Filters -->
            <aside class="sidebar sidebar-links library-sidebar">
                <div class="search-filters">
                    <div class="search-section">
                        <h3>
                            Keyword Search
                            @if(!empty($search))
                                <i class="fal fa-times-circle clear-filters-icon" onclick="clearSearch()" title="Clear Search"></i>
                            @endif
                        </h3>
                        <div class="search-box">
                            <input
                                type="text"
                                id="searchInput"
                                name="search"
                                placeholder="Search books, authors, topics..."
                                class="search-input input"
                                value="{{ $search ?? '' }}"
                                onkeypress="if(event.key === 'Enter') submitSearch()"
                            >
                            <button type="button" class="search-button" onclick="submitSearch()">
                                <i class="fal fa-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="entries-section">
                        <h3>Number of Entries</h3>
                        <div class="entries-selector">
                            <select id="entriesPerPage" class="entries-dropdown" onchange="changeEntriesPerPage(this.value)">
                                <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5 per page</option>
                                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 per page</option>
                                <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20 per page</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 per page</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 per page</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3>
                            Filter Results
                            @if(!empty(array_filter($filters)))
                                <i class="fal fa-times-circle clear-filters-icon" onclick="clearFilters()" title="Clear All Filters"></i>
                            @endif
                        </h3>

                    <!-- Hidden form for search -->
                    <form action="{{ route('library.index') }}" method="GET" id="library-search-form" style="display: none;">
                        <input type="hidden" name="search" value="{{ $search ?? '' }}">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
                    </form>

                    <form action="{{ route('library.index') }}" method="GET" id="filters-form">
                        <!-- Preserve search query -->
                        @if($search)
                            <input type="hidden" name="search" value="{{ $search }}">
                        @endif
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">

                        <!-- Purpose Filter -->
                        @if($availableSubjects->isNotEmpty())
                            @foreach($availableSubjects as $subjectType)
                                @if($subjectType->classificationValues->isNotEmpty())
                                    @php
                                        $activeSubjectsCount = count(array_intersect(
                                            $subjectType->classificationValues->pluck('id')->toArray(),
                                            $filters['subjects'] ?? []
                                        ));
                                        $isExpanded = $activeSubjectsCount > 0;
                                    @endphp
                                    <div class="filter-group {{ $activeSubjectsCount > 0 ? 'has-active-filters' : '' }}">
                                        <h4 class="filter-toggle" onclick="toggleFilterGroup(this)">
                                            <i class="fal {{ $isExpanded ? 'fa-chevron-down' : 'fa-chevron-right' }} toggle-icon"></i>
                                            {{ $subjectType->name }}
                                            @if($activeSubjectsCount > 0)
                                                <span class="active-filter-badge">{{ $activeSubjectsCount }} selected</span>
                                            @endif
                                        </h4>
                                        <div class="checkbox-group {{ $isExpanded ? '' : 'collapsed' }}">
                                            @foreach($subjectType->classificationValues as $classification)
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="subjects[]"
                                                        value="{{ $classification->id }}"
                                                        {{ in_array($classification->id, $filters['subjects'] ?? []) ? 'checked' : '' }}
                                                        onchange="submitFilterForm()"
                                                    >
                                                    &nbsp;{{ $classification->value }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <!-- Genre Filter -->
                        @if($availableGenres->isNotEmpty())
                            @foreach($availableGenres as $genreType)
                                @if($genreType->classificationValues->isNotEmpty())
                                    @php
                                        $activeGenresCount = count(array_intersect(
                                            $genreType->classificationValues->pluck('id')->toArray(),
                                            $filters['genres'] ?? []
                                        ));
                                        $isExpanded = $activeGenresCount > 0;
                                    @endphp
                                    <div class="filter-group {{ $activeGenresCount > 0 ? 'has-active-filters' : '' }}">
                                        <h4 class="filter-toggle" onclick="toggleFilterGroup(this)">
                                            <i class="fal {{ $isExpanded ? 'fa-chevron-down' : 'fa-chevron-right' }} toggle-icon"></i>
                                            {{ $genreType->name }}
                                            @if($activeGenresCount > 0)
                                                <span class="active-filter-badge">{{ $activeGenresCount }} selected</span>
                                            @endif
                                        </h4>
                                        <div class="checkbox-group {{ $isExpanded ? '' : 'collapsed' }}">
                                            @foreach($genreType->classificationValues as $classification)
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="genres[]"
                                                        value="{{ $classification->id }}"
                                                        {{ in_array($classification->id, $filters['genres'] ?? []) ? 'checked' : '' }}
                                                        onchange="submitFilterForm()"
                                                    >
                                                    &nbsp;{{ $classification->value }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <!-- Subgenre Filter (only shown if Genre is selected) -->
                        @if(!empty($filters['genres']) && $availableSubgenres->isNotEmpty())
                            @foreach($availableSubgenres as $subgenreType)
                                @if($subgenreType->classificationValues->isNotEmpty())
                                    @php
                                        $activeSubgenresCount = count(array_intersect(
                                            $subgenreType->classificationValues->pluck('id')->toArray(),
                                            $filters['subgenres'] ?? []
                                        ));
                                        $isExpanded = $activeSubgenresCount > 0;
                                    @endphp
                                    <div class="filter-group {{ $activeSubgenresCount > 0 ? 'has-active-filters' : '' }}">
                                        <h4 class="filter-toggle" onclick="toggleFilterGroup(this)">
                                            <i class="fal {{ $isExpanded ? 'fa-chevron-down' : 'fa-chevron-right' }} toggle-icon"></i>
                                            {{ $subgenreType->name }}
                                            @if($activeSubgenresCount > 0)
                                                <span class="active-filter-badge">{{ $activeSubgenresCount }} selected</span>
                                            @endif
                                        </h4>
                                        <div class="checkbox-group {{ $isExpanded ? '' : 'collapsed' }}">
                                            @foreach($subgenreType->classificationValues as $classification)
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="subgenres[]"
                                                        value="{{ $classification->id }}"
                                                        {{ in_array($classification->id, $filters['subgenres'] ?? []) ? 'checked' : '' }}
                                                        onchange="submitFilterForm()"
                                                    >
                                                    &nbsp;{{ $classification->value }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <!-- Area Filter -->
                        @if($availableAreas->isNotEmpty())
                            @foreach($availableAreas as $areaType)
                                @if($areaType->classificationValues->isNotEmpty())
                                    @php
                                        $activeAreasCount = count(array_intersect(
                                            $areaType->classificationValues->pluck('id')->toArray(),
                                            $filters['areas'] ?? []
                                        ));
                                        $isExpanded = $activeAreasCount > 0;
                                    @endphp
                                    <div class="filter-group {{ $activeAreasCount > 0 ? 'has-active-filters' : '' }}">
                                        <h4 class="filter-toggle" onclick="toggleFilterGroup(this)">
                                            <i class="fal {{ $isExpanded ? 'fa-chevron-down' : 'fa-chevron-right' }} toggle-icon"></i>
                                            Area
                                            @if($activeAreasCount > 0)
                                                <span class="active-filter-badge">{{ $activeAreasCount }} selected</span>
                                            @endif
                                        </h4>
                                        <div class="checkbox-group {{ $isExpanded ? '' : 'collapsed' }}">
                                            @foreach($areaType->classificationValues as $area)
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="areas[]"
                                                        value="{{ $area->id }}"
                                                        {{ in_array($area->id, $filters['areas'] ?? []) ? 'checked' : '' }}
                                                        onchange="submitFilterForm()"
                                                    >
                                                    &nbsp;{{ $area->value }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <!-- Physical Type Filter -->
                        @if($availablePhysicalTypes->isNotEmpty())
                            @php
                                $activeTypesCount = count($filters['types'] ?? []);
                                $isExpanded = $activeTypesCount > 0;
                            @endphp
                            <div class="filter-group {{ $activeTypesCount > 0 ? 'has-active-filters' : '' }}">
                                <h4 class="filter-toggle" onclick="toggleFilterGroup(this)">
                                    <i class="fal {{ $isExpanded ? 'fa-chevron-down' : 'fa-chevron-right' }} toggle-icon"></i>
                                    Type
                                    @if($activeTypesCount > 0)
                                        <span class="active-filter-badge">{{ $activeTypesCount }} selected</span>
                                    @endif
                                </h4>
                                <div class="checkbox-group {{ $isExpanded ? '' : 'collapsed' }}">
                                    @foreach($availablePhysicalTypes as $physicalType)
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="types[]"
                                                value="{{ $physicalType->id }}"
                                                {{ in_array($physicalType->id, $filters['types'] ?? []) ? 'checked' : '' }}
                                                onchange="submitFilterForm()"
                                            >
                                            &nbsp;{{ $physicalType->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Language Filter -->
                        @if($availableLanguages->isNotEmpty())
                            @php
                                $activeLanguagesCount = count($filters['languages'] ?? []);
                                $isExpanded = $activeLanguagesCount > 0;
                            @endphp
                            <div class="filter-group {{ $activeLanguagesCount > 0 ? 'has-active-filters' : '' }}">
                                <h4 class="filter-toggle" onclick="toggleFilterGroup(this)">
                                    <i class="fal {{ $isExpanded ? 'fa-chevron-down' : 'fa-chevron-right' }} toggle-icon"></i>
                                    Language
                                    @if($activeLanguagesCount > 0)
                                        <span class="active-filter-badge">{{ $activeLanguagesCount }} selected</span>
                                    @endif
                                </h4>
                                <div class="checkbox-group {{ $isExpanded ? '' : 'collapsed' }}">
                                    @foreach($availableLanguages as $language)
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="languages[]"
                                                value="{{ $language->code }}"
                                                {{ in_array($language->code, $filters['languages'] ?? []) ? 'checked' : '' }}
                                                onchange="submitFilterForm()"
                                            >
                                            &nbsp;{{ $language->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </form>
                </div>
            </aside>

        <!-- Main Content -->
        <div class="main_content library-content">
            <!-- Library Header -->
            <div class="library-header">
                <div class="results-info">
                    <h3>Current resources</h3>
                    <p id="results-count">Showing {{ $books->count() }} of {{ $books->total() }} books and other resources</p>
                </div>
                <div class="sort-options">
                    <label for="sort-select">Sort by:&nbsp;</label>
                    <select id="sort-select" onchange="changeSorting(this.value)">
                        <option value="random-asc" {{ $sortBy == 'random' ? 'selected' : '' }}>Random</option>
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
                                <img src="{{ $book->getThumbnailUrl() }}"
                                     alt="{{ $book->title }}"
                                     class="book-cover">
                            </td>
                            <td class="book-details-cell">
                                <div class="book-title">
                                    <a href="{{ route('library.show', $book->slug) }}"><span>{{ $book->title }}</span>
                                        @if($book->subtitle)
                                            &nbsp;&ndash; <span style="font-weight: normal">{{ $book->subtitle }}</span>
                                        @endif
                                    </a>
                                </div>
                                <div class="book-metadata">
                                    {{ $book->publication_year ?? 'N/A' }}
                                    @if($book->publisher)
                                        , {{ $book->publisher->name }}
                                    @endif
                                </div>
                                <div class="book-description">
                                    @php
                                        $descriptionParts = [];
                                        if($book->purposeClassifications->isNotEmpty()) {
                                            $descriptionParts[] = $book->purposeClassifications->pluck('value')->join(', ');
                                        }
                                        if($book->learnerLevelClassifications->isNotEmpty()) {
                                            $descriptionParts[] = $book->learnerLevelClassifications->pluck('value')->join(', ');
                                        }
                                        if($book->languages->isNotEmpty()) {
                                            $descriptionParts[] = $book->languages->pluck('name')->join(', ');
                                        }
                                    @endphp
                                    {{ implode(', ', $descriptionParts) }}
                                </div>
                                <div class="book-description">
                                    @if($book->access_level === 'full')
                                        Full access
                                    @elseif($book->access_level === 'limited')
                                        Limited access
                                    @else
                                        Unavailable
                                    @endif
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
                                <a href="{{ route('library.index') }}" class="button button-secondary">Clear filters</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($books->hasPages())
                <div class="pagination-container">
                    <div class="pagination-controls">
                        @if($books->onFirstPage())
                            <button class="pagination-btn nav-arrow" disabled>←</button>
                        @else
                            <a href="{{ $books->previousPageUrl() }}" class="pagination-btn nav-arrow">←</a>
                        @endif

                        @php
                            $currentPage = $books->currentPage();
                            $lastPage = $books->lastPage();
                            $onEachSide = 3; // Show 3 pages on each side of current page

                            // Calculate the start and end of the range
                            $start = max(1, $currentPage - $onEachSide);
                            $end = min($lastPage, $currentPage + $onEachSide);

                            // Adjust if we're near the beginning or end
                            if ($currentPage <= $onEachSide) {
                                $end = min($lastPage, $onEachSide * 2 + 1);
                            }
                            if ($currentPage >= $lastPage - $onEachSide) {
                                $start = max(1, $lastPage - ($onEachSide * 2));
                            }
                        @endphp

                        @if($start > 1)
                            <a href="{{ $books->url(1) }}" class="pagination-btn">1</a>
                            @if($start > 2)
                                <span class="pagination-ellipsis">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            @if($page == $currentPage)
                                <button class="pagination-btn active">{{ $page }}</button>
                            @else
                                <a href="{{ $books->url($page) }}" class="pagination-btn">{{ $page }}</a>
                            @endif
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="pagination-ellipsis">...</span>
                            @endif
                            <a href="{{ $books->url($lastPage) }}" class="pagination-btn">{{ $lastPage }}</a>
                        @endif

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
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Save and restore scroll position for filter changes
    (function() {
        // Restore scroll position IMMEDIATELY (before DOMContentLoaded)
        const savedScrollPos = sessionStorage.getItem('libraryScrollPosition');
        if (savedScrollPos !== null) {
            // Restore scroll as early as possible to prevent jump
            window.scrollTo(0, parseInt(savedScrollPos));

            // Also restore after DOM is ready (in case content shifts)
            window.addEventListener('load', function() {
                window.scrollTo(0, parseInt(savedScrollPos));
                sessionStorage.removeItem('libraryScrollPosition');
            });
        }
    })();

    document.addEventListener('DOMContentLoaded', function() {
        const filtersForm = document.getElementById('filters-form');

        // Save exact scroll position before filter form submission
        if (filtersForm) {
            filtersForm.addEventListener('submit', function() {
                sessionStorage.setItem('libraryScrollPosition', window.scrollY.toString());
            });
        }
    });

    function submitSearch() {
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('library-search-form');
        const hiddenSearchInput = searchForm.querySelector('input[name="search"]');

        // Update the hidden form's search value with the visible input's value
        hiddenSearchInput.value = searchInput.value;

        // Save current scroll position before submitting
        sessionStorage.setItem('libraryScrollPosition', window.scrollY.toString());

        // Submit the form
        searchForm.submit();
    }

    function clearSearch() {
        // Don't restore scroll position when clearing search - go to top
        sessionStorage.removeItem('libraryScrollPosition');

        // Build URL without search parameter but preserve other params
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        window.location.href = url.toString();
    }

    function toggleFilterGroup(element) {
        const icon = element.querySelector('.toggle-icon');
        const group = element.nextElementSibling;

        group.classList.toggle('collapsed');
        icon.classList.toggle('fa-chevron-right');
        icon.classList.toggle('fa-chevron-down');
    }

    function changeSorting(value) {
        // Don't restore scroll position when changing sort - go to top
        sessionStorage.removeItem('libraryScrollPosition');

        const [sortBy, sortDirection] = value.split('-');
        const url = new URL(window.location.href);
        url.searchParams.set('sort_by', sortBy);
        url.searchParams.set('sort_direction', sortDirection);
        window.location.href = url.toString();
    }

    function changeEntriesPerPage(value) {
        // Don't restore scroll position when changing entries per page - go to top
        sessionStorage.removeItem('libraryScrollPosition');

        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.delete('page'); // Reset to page 1
        window.location.href = url.toString();
    }

    function clearFilters() {
        // Don't restore scroll position when clearing filters - go to top
        sessionStorage.removeItem('libraryScrollPosition');
        window.location.href = '{{ route('library.index') }}';
    }

    function submitFilterForm() {
        sessionStorage.setItem('libraryScrollPosition', window.scrollY.toString());
        document.getElementById('filters-form').submit();
    }
</script>
@endpush
