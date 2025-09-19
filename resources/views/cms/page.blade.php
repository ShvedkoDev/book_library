@extends('layouts.cms')

@section('title', $page->meta_title ?: $page->title)
@section('description', $page->meta_description ?: Str::limit(strip_tags($page->excerpt), 160))

@section('main_class', 'with_sidebar print')

@section('content')
<div class="content print" role="main">
    <div class="title_banner with_sidebar">
        <div class="header-blurb container">
            <div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
                <span property="itemListElement" typeof="ListItem">
                    <a property="item" typeof="WebPage" title="Go to Micronesian Teachers Digital Library." href="{{ route('cms.page', 'home') }}" class="main-home">
                        <span property="name">Micronesian Teachers Digital Library</span>
                    </a>
                    <meta property="position" content="1">
                </span>
                @if($page->slug !== 'home')
                    <span class="breadcrumb-separator"> > </span>
                    <span property="itemListElement" typeof="ListItem">
                        <span property="name">{{ $page->title }}</span>
                        <meta property="position" content="2">
                    </span>
                @endif
            </div>
            <div class="handbook-header-menu">
                <h1>{{ $page->title }}</h1>
                @if($page->excerpt)
                    <p class="text-lg text-gray-600 mt-2">{{ $page->excerpt }}</p>
                @endif
            </div>
        </div>
        @if($page->featured_image)
            <aside class="sidebar header-image handbook-image">
                <img class="hupc-logo" src="{{ $page->featured_image }}" alt="{{ $page->title }} image">
            </aside>
        @else
            <aside class="sidebar header-image handbook-image">
                <img class="hupc-logo" src="https://picsum.photos/200/80?random=3DL" alt="Micronesian Teachers Digital Library logo">
                <img class="farm-logo" src="https://picsum.photos/120/60?random=12" alt="Education Initiative logo">
            </aside>
        @endif
    </div>

    <div class="page-content">
        <div class="container with_sidebar">
            <div class="main_content">
                <div class="section-content">
                    <div class="main-content">
                        @if($page->content_blocks && count($page->content_blocks) > 0)
                            @foreach($page->content_blocks as $block)
                                @php
                                    $blockType = $block['type'] ?? 'text';
                                    $blockContent = $block['content'] ?? [];
                                    $blockSettings = $block['settings'] ?? [];
                                @endphp

                                <div class="content-block content-block-{{ $blockType }}" @if(isset($blockSettings['id'])) id="{{ $blockSettings['id'] }}" @endif>
                                    @switch($blockType)
                                        @case('text')
                                            <x-cms.text-block :content="$blockContent" :settings="$blockSettings" />
                                            @break
                                        @case('image')
                                            <x-cms.image-block :content="$blockContent" :settings="$blockSettings" />
                                            @break
                                        @case('gallery')
                                            <x-cms.gallery-block :content="$blockContent" :settings="$blockSettings" />
                                            @break
                                        @case('video')
                                            <x-cms.video-block :content="$blockContent" :settings="$blockSettings" />
                                            @break
                                        @case('quote')
                                            <x-cms.quote-block :content="$blockContent" :settings="$blockSettings" />
                                            @break
                                        @case('code')
                                            <x-cms.code-block :content="$blockContent" :settings="$blockSettings" />
                                            @break
                                        @case('cta')
                                            <x-cms.cta-block :content="$blockContent" :settings="$blockSettings" />
                                            @break
                                        @case('divider')
                                            <x-cms.divider-block :content="$blockContent" :settings="$blockSettings" />
                                            @break
                                        @case('table')
                                            <x-cms.table-block :content="$blockContent" :settings="$blockSettings" />
                                            @break
                                        @case('accordion')
                                            <x-cms.accordion-block :content="$blockContent" :settings="$blockSettings" />
                                            @break
                                        @default
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                                <p class="text-yellow-800">Unknown block type: {{ $blockType }}</p>
                                            </div>
                                    @endswitch
                                </div>
                            @endforeach
                        @else
                            <div class="main-wysiwyg">
                                {!! $page->content !!}
                            </div>
                        @endif

                        @if($page->show_toc && $page->content_blocks)
                            @push('scripts')
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Generate table of contents if headings exist
                                    const headings = document.querySelectorAll('.main_content h2, .main_content h3, .main_content h4');
                                    if (headings.length > 0) {
                                        const toc = document.createElement('div');
                                        toc.className = 'table-of-contents bg-gray-50 p-4 rounded-lg mb-6';
                                        toc.innerHTML = '<h3 class="text-lg font-semibold mb-3">Table of Contents</h3>';

                                        const tocList = document.createElement('ul');
                                        tocList.className = 'space-y-1';

                                        headings.forEach((heading, index) => {
                                            const id = heading.id || `heading-${index}`;
                                            heading.id = id;

                                            const listItem = document.createElement('li');
                                            listItem.className = heading.tagName === 'H2' ? 'font-medium' : 'ml-4 text-sm';

                                            const link = document.createElement('a');
                                            link.href = `#${id}`;
                                            link.textContent = heading.textContent;
                                            link.className = 'text-blue-600 hover:text-blue-800';

                                            listItem.appendChild(link);
                                            tocList.appendChild(listItem);
                                        });

                                        toc.appendChild(tocList);
                                        document.querySelector('.main_content').prepend(toc);
                                    }
                                });
                            </script>
                            @endpush
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            @if($page->show_sidebar !== false)
                <aside class="sidebar" role="complementary">
                    <!-- Page Navigation -->
                    @if($page->categories && $page->categories->count() > 0)
                        <div class="widget widget-categories">
                            <h3 class="widget-title">Categories</h3>
                            <ul class="category-list">
                                @foreach($page->categories as $category)
                                    <li>
                                        <a href="{{ route('cms.category', $category->slug) }}"
                                           class="text-blue-600 hover:text-blue-800">
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Related Pages -->
                    @if(isset($relatedPages) && $relatedPages->count() > 0)
                        <div class="widget widget-related">
                            <h3 class="widget-title">Related Pages</h3>
                            <ul class="related-list">
                                @foreach($relatedPages as $relatedPage)
                                    <li class="mb-3">
                                        <a href="{{ route('cms.page', $relatedPage->slug) }}"
                                           class="block text-blue-600 hover:text-blue-800 font-medium">
                                            {{ $relatedPage->title }}
                                        </a>
                                        @if($relatedPage->excerpt)
                                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($relatedPage->excerpt, 80) }}</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Quick Links -->
                    <div class="widget widget-quick-links">
                        <h3 class="widget-title">Quick Links</h3>
                        <ul class="quick-links">
                            <li><a href="{{ route('cms.category', 'all') }}" class="text-blue-600 hover:text-blue-800">Browse Library</a></li>
                            <li><a href="{{ route('cms.search') }}" class="text-blue-600 hover:text-blue-800">Search Resources</a></li>
                            <li><a href="{{ route('cms.page', 'about') }}" class="text-blue-600 hover:text-blue-800">About MTDL</a></li>
                            <li><a href="{{ route('cms.page', 'contribute') }}" class="text-blue-600 hover:text-blue-800">Contribute</a></li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div class="widget widget-contact">
                        <h3 class="widget-title">Need Help?</h3>
                        <div class="contact-info text-sm text-gray-600">
                            <p><i class="fas fa-envelope mr-2"></i> <a href="mailto:info@mtdl.edu" class="text-blue-600 hover:text-blue-800">info@mtdl.edu</a></p>
                            <p><i class="fas fa-phone mr-2"></i> <a href="tel:+1-000-000-0000" class="text-blue-600 hover:text-blue-800">(000) 000-0000</a></p>
                        </div>
                    </div>
                </aside>
            @endif
        </div>
    </div>
</div>

<!-- Page-specific styles -->
@push('styles')
<style>
    /* Widget styles */
    .widget {
        @apply bg-white border border-gray-200 rounded-lg p-4 mb-6;
    }

    .widget-title {
        @apply text-lg font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-200;
    }

    .category-list, .related-list, .quick-links {
        @apply space-y-2;
    }

    .category-list li, .quick-links li {
        @apply border-l-2 border-blue-100 pl-3;
    }

    .category-list li:hover, .quick-links li:hover {
        @apply border-blue-300;
    }

    /* Content block spacing */
    .content-block {
        @apply mb-6;
    }

    .content-block:last-child {
        @apply mb-0;
    }

    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .with_sidebar {
            @apply grid-cols-1;
        }

        .sidebar {
            @apply order-first lg:order-last;
        }
    }
</style>
@endpush

<!-- Track page view if analytics is enabled -->
@if(config('cms.analytics.enabled', false))
    @push('scripts')
    <script>
        // Track page view
        fetch('{{ route("cms.api.page.view", $page->slug) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                page_id: {{ $page->id }},
                title: '{{ addslashes($page->title) }}',
                url: window.location.href,
                referrer: document.referrer
            })
        }).catch(console.error);
    </script>
    @endpush
@endif
@endsection