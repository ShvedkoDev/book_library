@extends('layouts.cms')

@section('title', 'Search Resources - Micronesian Teachers Digital Library')
@section('description', 'Search through our comprehensive collection of over 2,000 educational resources. Find books, guides, and materials by title, author, subject, or language.')

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
                    <span property="name">Search</span>
                    <meta property="position" content="2">
                </span>
            </div>
            <div class="handbook-header-menu">
                <h1>Search Resources</h1>
                <p class="text-lg text-gray-600 mt-2">
                    Find the perfect educational resources from our collection of over 2,000 books and materials.
                </p>
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
                <!-- Advanced Search Form -->
                <div class="search-form-section bg-white border border-gray-200 rounded-lg p-6 mb-8">
                    <form method="GET" action="{{ route('cms.search') }}" class="space-y-6">
                        <!-- Main Search -->
                        <div class="main-search">
                            <label for="main-search" class="block text-lg font-medium text-gray-900 mb-3">
                                What are you looking for?
                            </label>
                            <div class="relative">
                                <input type="text"
                                       id="main-search"
                                       name="q"
                                       value="{{ request('q') }}"
                                       placeholder="Enter keywords, title, author, or topic..."
                                       class="w-full px-4 py-4 pl-12 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       autofocus>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4">
                                    <i class="fas fa-search text-gray-400 text-xl"></i>
                                </div>
                                <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                        Search
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filters -->
                        <div class="advanced-filters">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Refine Your Search</h3>
                                <button type="button" id="toggle-filters" class="text-blue-600 hover:text-blue-800 font-medium">
                                    <span class="toggle-text">Show Filters</span>
                                    <i class="fas fa-chevron-down ml-1 toggle-icon"></i>
                                </button>
                            </div>

                            <div id="filter-section" class="filter-grid {{ request()->hasAny(['subject', 'grade', 'language', 'type', 'author', 'year']) ? '' : 'hidden' }}">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Subject -->
                                    <div class="filter-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject Area</label>
                                        <select name="subject" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Subjects</option>
                                            <option value="mathematics" {{ request('subject') === 'mathematics' ? 'selected' : '' }}>Mathematics</option>
                                            <option value="science" {{ request('subject') === 'science' ? 'selected' : '' }}>Science</option>
                                            <option value="language-arts" {{ request('subject') === 'language-arts' ? 'selected' : '' }}>Language Arts</option>
                                            <option value="social-studies" {{ request('subject') === 'social-studies' ? 'selected' : '' }}>Social Studies</option>
                                            <option value="cultural-studies" {{ request('subject') === 'cultural-studies' ? 'selected' : '' }}>Cultural Studies</option>
                                            <option value="environmental" {{ request('subject') === 'environmental' ? 'selected' : '' }}>Environmental Studies</option>
                                            <option value="arts" {{ request('subject') === 'arts' ? 'selected' : '' }}>Arts & Crafts</option>
                                            <option value="health" {{ request('subject') === 'health' ? 'selected' : '' }}>Health & Wellness</option>
                                        </select>
                                    </div>

                                    <!-- Grade Level -->
                                    <div class="filter-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Grade Level</label>
                                        <select name="grade" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Grades</option>
                                            <option value="pre-k" {{ request('grade') === 'pre-k' ? 'selected' : '' }}>Pre-K</option>
                                            <option value="k-2" {{ request('grade') === 'k-2' ? 'selected' : '' }}>K-2</option>
                                            <option value="3-5" {{ request('grade') === '3-5' ? 'selected' : '' }}>3-5</option>
                                            <option value="6-8" {{ request('grade') === '6-8' ? 'selected' : '' }}>6-8</option>
                                            <option value="9-12" {{ request('grade') === '9-12' ? 'selected' : '' }}>9-12</option>
                                            <option value="adult" {{ request('grade') === 'adult' ? 'selected' : '' }}>Adult Education</option>
                                            <option value="all-ages" {{ request('grade') === 'all-ages' ? 'selected' : '' }}>All Ages</option>
                                        </select>
                                    </div>

                                    <!-- Language -->
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
                                            <option value="palauan" {{ request('language') === 'palauan' ? 'selected' : '' }}>Palauan</option>
                                        </select>
                                    </div>

                                    <!-- Resource Type -->
                                    <div class="filter-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Resource Type</label>
                                        <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Types</option>
                                            <option value="book" {{ request('type') === 'book' ? 'selected' : '' }}>Book</option>
                                            <option value="workbook" {{ request('type') === 'workbook' ? 'selected' : '' }}>Workbook</option>
                                            <option value="guide" {{ request('type') === 'guide' ? 'selected' : '' }}>Teaching Guide</option>
                                            <option value="activity" {{ request('type') === 'activity' ? 'selected' : '' }}>Activity Book</option>
                                            <option value="assessment" {{ request('type') === 'assessment' ? 'selected' : '' }}>Assessment</option>
                                            <option value="reference" {{ request('type') === 'reference' ? 'selected' : '' }}>Reference Material</option>
                                        </select>
                                    </div>

                                    <!-- Author -->
                                    <div class="filter-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Author</label>
                                        <input type="text"
                                               name="author"
                                               value="{{ request('author') }}"
                                               placeholder="Enter author name..."
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <!-- Publication Year -->
                                    <div class="filter-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Publication Year</label>
                                        <div class="flex space-x-2">
                                            <input type="number"
                                                   name="year_from"
                                                   value="{{ request('year_from') }}"
                                                   placeholder="From"
                                                   min="1950"
                                                   max="{{ date('Y') }}"
                                                   class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <input type="number"
                                                   name="year_to"
                                                   value="{{ request('year_to') }}"
                                                   placeholder="To"
                                                   min="1950"
                                                   max="{{ date('Y') }}"
                                                   class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </div>

                                <!-- Search Options -->
                                <div class="search-options mt-6 pt-6 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Search Options</h4>
                                    <div class="flex flex-wrap gap-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="exact_phrase" value="1" {{ request('exact_phrase') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Exact phrase</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="title_only" value="1" {{ request('title_only') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Search in titles only</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="include_description" value="1" {{ request('include_description', true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Include descriptions</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="action-buttons mt-6 flex flex-wrap gap-3">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                        <i class="fas fa-search mr-2"></i>Search Resources
                                    </button>
                                    <a href="{{ route('cms.search') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition-colors">
                                        <i class="fas fa-times mr-2"></i>Clear All
                                    </a>
                                    <button type="button" id="save-search" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                        <i class="fas fa-save mr-2"></i>Save Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Search Results -->
                @if(request('q') || request()->hasAny(['subject', 'grade', 'language', 'type', 'author', 'year_from', 'year_to']))
                    <div class="search-results-section">
                        <!-- Results Header -->
                        <div class="results-header mb-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900 mb-2">
                                        Search Results
                                        @if(request('q'))
                                            for "{{ request('q') }}"
                                        @endif
                                    </h2>
                                    <p class="text-gray-600">
                                        @if(isset($results))
                                            Found {{ $results->total() }} resources
                                        @else
                                            Searching...
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="sort-options">
                                        <form method="GET" action="{{ route('cms.search') }}" class="inline">
                                            @foreach(request()->except('sort') as $key => $value)
                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                            @endforeach
                                            <select name="sort" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="relevance" {{ request('sort') === 'relevance' ? 'selected' : '' }}>Most Relevant</option>
                                                <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>Title A-Z</option>
                                                <option value="author" {{ request('sort') === 'author' ? 'selected' : '' }}>Author A-Z</option>
                                                <option value="year" {{ request('sort') === 'year' ? 'selected' : '' }}>Newest First</option>
                                                <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Filters -->
                            @if(request()->hasAny(['subject', 'grade', 'language', 'type', 'author', 'year_from', 'year_to', 'exact_phrase', 'title_only']))
                                <div class="active-filters mt-4 p-4 bg-blue-50 rounded-lg">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-sm font-medium text-blue-900">Active Filters:</h3>
                                        <a href="{{ route('cms.search', ['q' => request('q')]) }}" class="text-sm text-blue-600 hover:text-blue-800">Clear All Filters</a>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(['subject', 'grade', 'language', 'type', 'author'] as $filter)
                                            @if(request($filter))
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ ucwords(str_replace('-', ' ', $filter)) }}: {{ request($filter) }}
                                                    <a href="{{ route('cms.search', array_merge(request()->except($filter), ['q' => request('q')])) }}" class="ml-1 text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-times text-xs"></i>
                                                    </a>
                                                </span>
                                            @endif
                                        @endforeach
                                        @if(request('year_from') || request('year_to'))
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Year: {{ request('year_from', 'Any') }} - {{ request('year_to', 'Any') }}
                                                <a href="{{ route('cms.search', array_merge(request()->except(['year_from', 'year_to']), ['q' => request('q')])) }}" class="ml-1 text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-times text-xs"></i>
                                                </a>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Results List -->
                        @if(isset($results) && $results->count() > 0)
                            <div class="results-list space-y-6">
                                @foreach($results as $result)
                                    <article class="result-item bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                        <div class="flex">
                                            @if($result->featured_image)
                                                <div class="result-image w-24 h-32 flex-shrink-0 mr-4">
                                                    <img src="{{ $result->featured_image }}"
                                                         alt="{{ $result->title }}"
                                                         class="w-full h-full object-cover rounded">
                                                </div>
                                            @endif

                                            <div class="result-content flex-1">
                                                <div class="result-meta mb-2">
                                                    @if($result->categories->count() > 0)
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach($result->categories->take(3) as $category)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                    {{ $category->name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>

                                                <h3 class="result-title text-xl font-semibold text-gray-900 mb-2">
                                                    <a href="{{ route('cms.page', $result->slug) }}" class="hover:text-blue-600 transition-colors">
                                                        {!! $result->highlighted_title ?? $result->title !!}
                                                    </a>
                                                </h3>

                                                @if($result->excerpt)
                                                    <p class="result-excerpt text-gray-600 mb-3">
                                                        {!! $result->highlighted_excerpt ?? Str::limit($result->excerpt, 200) !!}
                                                    </p>
                                                @endif

                                                <div class="result-details text-sm text-gray-500 mb-3">
                                                    <div class="flex flex-wrap gap-4">
                                                        @if($result->author)
                                                            <span><i class="fas fa-user mr-1"></i>{{ $result->author }}</span>
                                                        @endif
                                                        @if($result->published_year)
                                                            <span><i class="fas fa-calendar mr-1"></i>{{ $result->published_year }}</span>
                                                        @endif
                                                        @if($result->language)
                                                            <span><i class="fas fa-language mr-1"></i>{{ ucfirst($result->language) }}</span>
                                                        @endif
                                                        @if($result->view_count)
                                                            <span><i class="fas fa-eye mr-1"></i>{{ number_format($result->view_count) }} views</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="result-actions">
                                                    <a href="{{ route('cms.page', $result->slug) }}"
                                                       class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                                                        View Resource
                                                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if($results->hasPages())
                                <div class="pagination-wrapper mt-8">
                                    {{ $results->appends(request()->query())->links('cms.partials.pagination') }}
                                </div>
                            @endif

                        @elseif(isset($results))
                            <!-- No Results -->
                            <div class="no-results text-center py-12">
                                <div class="max-w-md mx-auto">
                                    <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No results found</h3>
                                    <p class="text-gray-600 mb-6">
                                        We couldn't find any resources matching your search criteria.
                                    </p>
                                    <div class="space-y-3">
                                        <div class="text-sm text-gray-500">
                                            <p class="font-medium mb-2">Try:</p>
                                            <ul class="space-y-1 text-left">
                                                <li>• Using different or broader keywords</li>
                                                <li>• Checking your spelling</li>
                                                <li>• Removing some filters</li>
                                                <li>• Using synonyms or related terms</li>
                                                <li>• Searching in all languages</li>
                                            </ul>
                                        </div>
                                        <a href="{{ route('cms.category', 'all') }}"
                                           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                            Browse All Resources
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Search Suggestions -->
                    <div class="search-suggestions">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <!-- Popular Searches -->
                            <div class="suggestion-card bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                    <i class="fas fa-fire text-orange-500 mr-2"></i>Popular Searches
                                </h3>
                                <ul class="space-y-2">
                                    <li><a href="{{ route('cms.search', ['q' => 'mathematics']) }}" class="text-blue-600 hover:text-blue-800">Mathematics</a></li>
                                    <li><a href="{{ route('cms.search', ['q' => 'cultural studies']) }}" class="text-blue-600 hover:text-blue-800">Cultural Studies</a></li>
                                    <li><a href="{{ route('cms.search', ['q' => 'language arts']) }}" class="text-blue-600 hover:text-blue-800">Language Arts</a></li>
                                    <li><a href="{{ route('cms.search', ['q' => 'science']) }}" class="text-blue-600 hover:text-blue-800">Science</a></li>
                                    <li><a href="{{ route('cms.search', ['language' => 'chuukese']) }}" class="text-blue-600 hover:text-blue-800">Chuukese Resources</a></li>
                                </ul>
                            </div>

                            <!-- Browse by Subject -->
                            <div class="suggestion-card bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                    <i class="fas fa-book text-blue-500 mr-2"></i>Browse by Subject
                                </h3>
                                <ul class="space-y-2">
                                    <li><a href="{{ route('cms.search', ['subject' => 'mathematics']) }}" class="text-blue-600 hover:text-blue-800">Mathematics</a></li>
                                    <li><a href="{{ route('cms.search', ['subject' => 'science']) }}" class="text-blue-600 hover:text-blue-800">Science</a></li>
                                    <li><a href="{{ route('cms.search', ['subject' => 'language-arts']) }}" class="text-blue-600 hover:text-blue-800">Language Arts</a></li>
                                    <li><a href="{{ route('cms.search', ['subject' => 'social-studies']) }}" class="text-blue-600 hover:text-blue-800">Social Studies</a></li>
                                    <li><a href="{{ route('cms.search', ['subject' => 'cultural-studies']) }}" class="text-blue-600 hover:text-blue-800">Cultural Studies</a></li>
                                </ul>
                            </div>

                            <!-- Browse by Language -->
                            <div class="suggestion-card bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                    <i class="fas fa-language text-green-500 mr-2"></i>Browse by Language
                                </h3>
                                <ul class="space-y-2">
                                    <li><a href="{{ route('cms.search', ['language' => 'english']) }}" class="text-blue-600 hover:text-blue-800">English</a></li>
                                    <li><a href="{{ route('cms.search', ['language' => 'chuukese']) }}" class="text-blue-600 hover:text-blue-800">Chuukese</a></li>
                                    <li><a href="{{ route('cms.search', ['language' => 'pohnpeian']) }}" class="text-blue-600 hover:text-blue-800">Pohnpeian</a></li>
                                    <li><a href="{{ route('cms.search', ['language' => 'yapese']) }}" class="text-blue-600 hover:text-blue-800">Yapese</a></li>
                                    <li><a href="{{ route('cms.search', ['language' => 'kosraean']) }}" class="text-blue-600 hover:text-blue-800">Kosraean</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="quick-stats bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Our Collection</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                <div class="stat-item">
                                    <div class="text-2xl font-bold text-blue-600">2,000+</div>
                                    <div class="text-sm text-gray-600">Total Resources</div>
                                </div>
                                <div class="stat-item">
                                    <div class="text-2xl font-bold text-green-600">6</div>
                                    <div class="text-sm text-gray-600">Languages</div>
                                </div>
                                <div class="stat-item">
                                    <div class="text-2xl font-bold text-purple-600">8</div>
                                    <div class="text-sm text-gray-600">Subject Areas</div>
                                </div>
                                <div class="stat-item">
                                    <div class="text-2xl font-bold text-orange-600">K-12+</div>
                                    <div class="text-sm text-gray-600">Grade Levels</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <aside class="sidebar" role="complementary">
                <!-- Search Tips -->
                <div class="widget widget-tips">
                    <h3 class="widget-title">Search Tips</h3>
                    <div class="tips-content text-sm text-gray-600 space-y-3">
                        <div class="tip-item">
                            <h4 class="font-medium text-gray-900">Use quotes for exact phrases</h4>
                            <p class="text-xs">Example: "marine biology"</p>
                        </div>
                        <div class="tip-item">
                            <h4 class="font-medium text-gray-900">Use multiple keywords</h4>
                            <p class="text-xs">Example: ocean science grade 5</p>
                        </div>
                        <div class="tip-item">
                            <h4 class="font-medium text-gray-900">Try synonyms</h4>
                            <p class="text-xs">Example: math, mathematics, arithmetic</p>
                        </div>
                        <div class="tip-item">
                            <h4 class="font-medium text-gray-900">Use filters</h4>
                            <p class="text-xs">Narrow results by subject, grade, or language</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Searches -->
                @if(session('recent_searches'))
                    <div class="widget widget-recent">
                        <h3 class="widget-title">Recent Searches</h3>
                        <ul class="recent-list space-y-2">
                            @foreach(array_slice(session('recent_searches', []), 0, 5) as $search)
                                <li>
                                    <a href="{{ route('cms.search', ['q' => $search]) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                        {{ $search }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Contact -->
                <div class="widget widget-contact">
                    <h3 class="widget-title">Can't Find Something?</h3>
                    <div class="contact-content text-sm text-gray-600">
                        <p class="mb-3">Our librarians are here to help you find the perfect resources.</p>
                        <a href="mailto:info@mtdl.edu" class="text-blue-600 hover:text-blue-800 font-medium">
                            <i class="fas fa-envelope mr-2"></i>Contact a Librarian
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle filters
    const toggleFilters = document.getElementById('toggle-filters');
    const filterSection = document.getElementById('filter-section');
    const toggleIcon = toggleFilters.querySelector('.toggle-icon');
    const toggleText = toggleFilters.querySelector('.toggle-text');

    toggleFilters.addEventListener('click', function() {
        const isHidden = filterSection.classList.contains('hidden');

        if (isHidden) {
            filterSection.classList.remove('hidden');
            toggleIcon.classList.remove('fa-chevron-down');
            toggleIcon.classList.add('fa-chevron-up');
            toggleText.textContent = 'Hide Filters';
        } else {
            filterSection.classList.add('hidden');
            toggleIcon.classList.remove('fa-chevron-up');
            toggleIcon.classList.add('fa-chevron-down');
            toggleText.textContent = 'Show Filters';
        }
    });

    // Save search functionality
    const saveSearchBtn = document.getElementById('save-search');
    if (saveSearchBtn) {
        saveSearchBtn.addEventListener('click', function() {
            // In a real implementation, this would save to user preferences or localStorage
            alert('Search saved! You can access saved searches from your dashboard.');
        });
    }

    // Auto-submit on sort change
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
.widget {
    @apply bg-white border border-gray-200 rounded-lg p-4 mb-6;
}

.widget-title {
    @apply text-lg font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-200;
}

.suggestion-card:hover {
    @apply shadow-md;
    transition: box-shadow 0.2s ease-in-out;
}

.result-item:hover {
    @apply shadow-md;
    transition: box-shadow 0.2s ease-in-out;
}

/* Highlighted search terms */
.highlight {
    @apply bg-yellow-200 font-medium;
}

/* Filter section animation */
#filter-section {
    transition: all 0.3s ease-in-out;
}

#filter-section.hidden {
    max-height: 0;
    overflow: hidden;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filter-grid {
        @apply grid-cols-1;
    }

    .results-header {
        @apply flex-col items-start space-y-4;
    }

    .result-item .flex {
        @apply flex-col;
    }

    .result-image {
        @apply w-full h-48 mb-4;
    }
}
</style>
@endpush
@endsection