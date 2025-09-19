@extends('layouts.cms')

@section('title', ($category ? $category->name . ' - ' : '') . 'Resource Library')
@section('description', $category ? $category->description : 'Browse our comprehensive collection of over 2,000 educational resources organized by categories, subjects, and languages.')

@section('main_class', 'with_sidebar')

@section('content')
<div class="content" role="main">
    <div class="title_banner with_sidebar">
        <div class="header-blurb container">
            <div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
                <span property="itemListElement" typeof="ListItem">
                    <a property="item" typeof="WebPage" title="Go to Micronesian Teachers Digital Library." href="{{ route('cms.page', 'home') }}" class="main-home">
                        <span property="name">Micronesian Teachers Digital Library</span>
                    </a>
                    <meta property="position" content="1">
                </span>
                <span class="breadcrumb-separator"> > </span>
                <span property="itemListElement" typeof="ListItem">
                    <a property="item" typeof="WebPage" href="{{ route('cms.category', 'all') }}">
                        <span property="name">Resource Library</span>
                    </a>
                    <meta property="position" content="2">
                </span>
                @if($category)
                    <span class="breadcrumb-separator"> > </span>
                    <span property="itemListElement" typeof="ListItem">
                        <span property="name">{{ $category->name }}</span>
                        <meta property="position" content="3">
                    </span>
                @endif
            </div>
            <div class="handbook-header-menu">
                <h1>
                    @if($category)
                        {{ $category->name }}
                    @else
                        Resource Library
                    @endif
                </h1>
                @if($category && $category->description)
                    <p class="text-lg text-gray-600 mt-2">{{ $category->description }}</p>
                @else
                    <p class="text-lg text-gray-600 mt-2">
                        Explore our comprehensive collection of over 2,000 educational resources
                        organized by categories, subjects, and languages.
                    </p>
                @endif
            </div>
        </div>
        <aside class="sidebar header-image handbook-image">
            <img class="hupc-logo" src="https://picsum.photos/200/80?random=3DL" alt="Micronesian Teachers Digital Library logo">
            <img class="farm-logo" src="https://picsum.photos/120/60?random=12" alt="Education Initiative logo">
        </aside>
    </div>

    <div class="page-content">
        <div class="container with_sidebar">
            <div class="main_content">
                <!-- Search and Filters -->
                <div class="filters-section bg-white border border-gray-200 rounded-lg p-6 mb-8">
                    <form method="GET" action="{{ route('cms.category', $category?->slug ?? 'all') }}" class="space-y-6">
                        <!-- Search Bar -->
                        <div class="search-bar">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                Search Resources
                            </label>
                            <div class="relative">
                                <input type="text"
                                       id="search"
                                       name="q"
                                       value="{{ request('q') }}"
                                       placeholder="Search by title, author, or description..."
                                       class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Grid -->
                        <div class="filters-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Subject Filter -->
                            <div class="filter-group">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                                <select name="subject" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Subjects</option>
                                    <option value="mathematics" {{ request('subject') === 'mathematics' ? 'selected' : '' }}>Mathematics</option>
                                    <option value="science" {{ request('subject') === 'science' ? 'selected' : '' }}>Science</option>
                                    <option value="language-arts" {{ request('subject') === 'language-arts' ? 'selected' : '' }}>Language Arts</option>
                                    <option value="social-studies" {{ request('subject') === 'social-studies' ? 'selected' : '' }}>Social Studies</option>
                                    <option value="cultural-studies" {{ request('subject') === 'cultural-studies' ? 'selected' : '' }}>Cultural Studies</option>
                                    <option value="environmental" {{ request('subject') === 'environmental' ? 'selected' : '' }}>Environmental</option>
                                </select>
                            </div>

                            <!-- Grade Level Filter -->
                            <div class="filter-group">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grade Level</label>
                                <select name="grade" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Grades</option>
                                    <option value="k-2" {{ request('grade') === 'k-2' ? 'selected' : '' }}>K-2</option>
                                    <option value="3-5" {{ request('grade') === '3-5' ? 'selected' : '' }}>3-5</option>
                                    <option value="6-8" {{ request('grade') === '6-8' ? 'selected' : '' }}>6-8</option>
                                    <option value="9-12" {{ request('grade') === '9-12' ? 'selected' : '' }}>9-12</option>
                                    <option value="adult" {{ request('grade') === 'adult' ? 'selected' : '' }}>Adult</option>
                                </select>
                            </div>

                            <!-- Language Filter -->
                            <div class="filter-group">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                <select name="language" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Languages</option>
                                    <option value="english" {{ request('language') === 'english' ? 'selected' : '' }}>English</option>
                                    <option value="chuukese" {{ request('language') === 'chuukese' ? 'selected' : '' }}>Chuukese</option>
                                    <option value="pohnpeian" {{ request('language') === 'pohnpeian' ? 'selected' : '' }}>Pohnpeian</option>
                                    <option value="yapese" {{ request('language') === 'yapese' ? 'selected' : '' }}>Yapese</option>
                                    <option value="kosraean" {{ request('language') === 'kosraean' ? 'selected' : '' }}>Kosraean</option>
                                    <option value="marshallese" {{ request('language') === 'marshallese' ? 'selected' : '' }}>Marshallese</option>
                                </select>
                            </div>

                            <!-- Resource Type Filter -->
                            <div class="filter-group">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Resource Type</label>
                                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Types</option>
                                    <option value="book" {{ request('type') === 'book' ? 'selected' : '' }}>Book</option>
                                    <option value="workbook" {{ request('type') === 'workbook' ? 'selected' : '' }}>Workbook</option>
                                    <option value="guide" {{ request('type') === 'guide' ? 'selected' : '' }}>Teaching Guide</option>
                                    <option value="activity" {{ request('type') === 'activity' ? 'selected' : '' }}>Activity</option>
                                    <option value="assessment" {{ request('type') === 'assessment' ? 'selected' : '' }}>Assessment</option>
                                </select>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-search mr-2"></i>Apply Filters
                            </button>
                            <a href="{{ route('cms.category', $category?->slug ?? 'all') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-times mr-2"></i>Clear Filters
                            </a>
                            <div class="ml-auto">
                                <select name="sort" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>Sort by Title</option>
                                    <option value="author" {{ request('sort') === 'author' ? 'selected' : '' }}>Sort by Author</option>
                                    <option value="year" {{ request('sort') === 'year' ? 'selected' : '' }}>Sort by Year</option>
                                    <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                                    <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Recently Added</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results Summary -->
                <div class="results-summary mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-gray-600">
                                Showing {{ $pages->firstItem() ?? 0 }} to {{ $pages->lastItem() ?? 0 }} of {{ $pages->total() }} results
                                @if(request('q'))
                                    for "<strong>{{ request('q') }}</strong>"
                                @endif
                                @if($category)
                                    in <strong>{{ $category->name }}</strong>
                                @endif
                            </p>
                        </div>
                        <div class="view-toggle flex border border-gray-300 rounded-lg overflow-hidden">
                            <button type="button" class="view-btn px-3 py-2 text-sm {{ request('view') !== 'list' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}" data-view="grid">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button type="button" class="view-btn px-3 py-2 text-sm {{ request('view') === 'list' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}" data-view="list">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Results Grid/List -->
                @if($pages->count() > 0)
                    <div class="results-container" id="results-container">
                        <div class="results-grid {{ request('view') === 'list' ? 'list-view' : 'grid-view' }} grid gap-6 {{ request('view') === 'list' ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' }}">
                            @foreach($pages as $page)
                                <article class="resource-card bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                                    @if($page->featured_image)
                                        <div class="resource-image {{ request('view') === 'list' ? 'w-32 flex-shrink-0' : 'h-48' }}">
                                            <img src="{{ $page->featured_image }}"
                                                 alt="{{ $page->title }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                    @endif

                                    <div class="resource-content p-6 {{ request('view') === 'list' ? 'flex-1' : '' }}">
                                        <div class="resource-meta mb-2">
                                            @if($page->categories->count() > 0)
                                                <div class="flex flex-wrap gap-2 mb-2">
                                                    @foreach($page->categories->take(2) as $cat)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $cat->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($page->categories->count() > 2)
                                                        <span class="text-xs text-gray-500">+{{ $page->categories->count() - 2 }} more</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <h3 class="resource-title text-lg font-semibold text-gray-900 mb-2">
                                            <a href="{{ route('cms.page', $page->slug) }}" class="hover:text-blue-600 transition-colors">
                                                {{ $page->title }}
                                            </a>
                                        </h3>

                                        @if($page->excerpt)
                                            <p class="resource-excerpt text-gray-600 text-sm mb-3 {{ request('view') === 'list' ? '' : 'line-clamp-3' }}">
                                                {{ Str::limit($page->excerpt, request('view') === 'list' ? 200 : 120) }}
                                            </p>
                                        @endif

                                        <div class="resource-footer">
                                            <div class="flex items-center justify-between">
                                                <div class="resource-actions">
                                                    <a href="{{ route('cms.page', $page->slug) }}"
                                                       class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-sm">
                                                        View Resource
                                                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                                    </a>
                                                </div>

                                                <div class="resource-stats text-xs text-gray-500">
                                                    @if($page->view_count)
                                                        <span class="flex items-center">
                                                            <i class="fas fa-eye mr-1"></i>
                                                            {{ number_format($page->view_count) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($pages->hasPages())
                            <div class="pagination-wrapper mt-8">
                                {{ $pages->appends(request()->query())->links('cms.partials.pagination') }}
                            </div>
                        @endif
                    </div>
                @else
                    <!-- No Results -->
                    <div class="no-results text-center py-12">
                        <div class="max-w-md mx-auto">
                            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No resources found</h3>
                            <p class="text-gray-600 mb-6">
                                @if(request('q'))
                                    We couldn't find any resources matching "<strong>{{ request('q') }}</strong>".
                                @else
                                    No resources match your current filter criteria.
                                @endif
                            </p>
                            <div class="space-y-3">
                                <a href="{{ route('cms.category', $category?->slug ?? 'all') }}"
                                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                    Clear All Filters
                                </a>
                                <div class="text-sm text-gray-500">
                                    <p>Try:</p>
                                    <ul class="mt-2 space-y-1">
                                        <li>• Using different search terms</li>
                                        <li>• Removing some filters</li>
                                        <li>• Browsing all categories</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <aside class="sidebar" role="complementary">
                <!-- Category Navigation -->
                <div class="widget widget-categories">
                    <h3 class="widget-title">Categories</h3>
                    <ul class="category-list space-y-2">
                        <li class="{{ !$category ? 'font-semibold text-blue-600' : '' }}">
                            <a href="{{ route('cms.category', 'all') }}" class="block py-1 {{ !$category ? 'text-blue-600' : 'text-gray-700 hover:text-blue-600' }}">
                                <i class="fas fa-folder mr-2"></i>All Categories
                                <span class="float-right text-sm text-gray-500">{{ $totalCount ?? 0 }}</span>
                            </a>
                        </li>
                        @foreach($categories as $cat)
                            <li class="{{ $category && $category->id === $cat->id ? 'font-semibold text-blue-600' : '' }}">
                                <a href="{{ route('cms.category', $cat->slug) }}" class="block py-1 {{ $category && $category->id === $cat->id ? 'text-blue-600' : 'text-gray-700 hover:text-blue-600' }}">
                                    <i class="fas fa-folder mr-2" style="color: {{ $cat->color ?? '#6b7280' }}"></i>
                                    {{ $cat->name }}
                                    <span class="float-right text-sm text-gray-500">{{ $cat->pages_count ?? 0 }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Popular Resources -->
                @if(isset($popularPages) && $popularPages->count() > 0)
                    <div class="widget widget-popular">
                        <h3 class="widget-title">Popular Resources</h3>
                        <ul class="popular-list space-y-3">
                            @foreach($popularPages as $popularPage)
                                <li>
                                    <a href="{{ route('cms.page', $popularPage->slug) }}" class="block group">
                                        <h4 class="text-sm font-medium text-gray-900 group-hover:text-blue-600 mb-1">
                                            {{ Str::limit($popularPage->title, 50) }}
                                        </h4>
                                        <div class="flex items-center text-xs text-gray-500">
                                            <i class="fas fa-eye mr-1"></i>
                                            {{ number_format($popularPage->view_count) }} views
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="widget widget-actions">
                    <h3 class="widget-title">Quick Actions</h3>
                    <div class="action-buttons space-y-2">
                        <a href="{{ route('cms.search') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-search mr-2"></i>Advanced Search
                        </a>
                        <a href="{{ route('cms.page', 'contribute') }}" class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-plus mr-2"></i>Contribute Resource
                        </a>
                        <a href="{{ route('cms.feed') }}" class="block w-full bg-orange-600 hover:bg-orange-700 text-white text-center py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-rss mr-2"></i>RSS Feed
                        </a>
                    </div>
                </div>

                <!-- Help -->
                <div class="widget widget-help">
                    <h3 class="widget-title">Need Help?</h3>
                    <div class="help-content text-sm text-gray-600 space-y-2">
                        <p><strong>Search Tips:</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li>Use quotes for exact phrases</li>
                            <li>Try different keywords</li>
                            <li>Check spelling</li>
                            <li>Use filters to narrow results</li>
                        </ul>
                        <div class="pt-3">
                            <a href="{{ route('cms.page', 'help') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                View Full Help Guide
                            </a>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<!-- View Toggle Script -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const resultsContainer = document.getElementById('results-container');
    const resultsGrid = resultsContainer?.querySelector('.results-grid');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;

            // Update button states
            viewButtons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-white', 'text-gray-700', 'hover:bg-gray-50');
            });
            this.classList.remove('bg-white', 'text-gray-700', 'hover:bg-gray-50');
            this.classList.add('bg-blue-600', 'text-white');

            // Update grid classes
            if (resultsGrid) {
                if (view === 'list') {
                    resultsGrid.classList.remove('grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3');
                    resultsGrid.classList.add('grid-cols-1', 'list-view');
                    resultsGrid.classList.remove('grid-view');
                } else {
                    resultsGrid.classList.remove('grid-cols-1', 'list-view');
                    resultsGrid.classList.add('grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'grid-view');
                }
            }

            // Update URL parameter
            const url = new URL(window.location);
            if (view === 'list') {
                url.searchParams.set('view', 'list');
            } else {
                url.searchParams.delete('view');
            }
            window.history.replaceState({}, '', url);
        });
    });

    // Auto-submit form when sort changes
    const sortSelect = document.querySelector('select[name="sort"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
});
</script>
@endpush

@push('styles')
<style>
/* List view styles */
.list-view .resource-card {
    @apply flex flex-row items-start;
}

.list-view .resource-image {
    @apply w-32 h-24 flex-shrink-0;
}

.list-view .resource-content {
    @apply flex-1;
}

.list-view .resource-excerpt {
    @apply line-clamp-2;
}

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Widget styles */
.widget {
    @apply bg-white border border-gray-200 rounded-lg p-4 mb-6;
}

.widget-title {
    @apply text-lg font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-200;
}

.category-list li {
    @apply border-l-2 border-transparent pl-3 transition-colors;
}

.category-list li:hover {
    @apply border-blue-300;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filters-grid {
        @apply grid-cols-1 gap-4;
    }

    .results-summary {
        @apply flex-col items-start space-y-3;
    }

    .view-toggle {
        @apply self-end;
    }

    .list-view .resource-card {
        @apply flex-col;
    }

    .list-view .resource-image {
        @apply w-full h-48;
    }
}
</style>
@endpush
@endsection