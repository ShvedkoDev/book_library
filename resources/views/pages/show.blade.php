@extends('layouts.library')

@section('title', $page->title . ' - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('description', $page->meta_description ?? $page->excerpt)
@section('og_type', 'article')

@push('styles')
<style>
    /* Preview Banner */
    .preview-banner {
        background: #fef3c7;
        border: 2px solid #f59e0b;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #92400e;
    }

    /* Page Content Typography */
    .main_content.page-content-wrapper .page-body {
        line-height: 1.8;
    }

    .main_content.page-content-wrapper .page-body h2 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 2.5rem 0 1rem 0;
        padding-top: 1rem;
        scroll-margin-top: 2rem;
    }

    .main_content.page-content-wrapper .page-body h3 {
        font-size: 1.4rem;
        font-weight: 600;
        margin: 2rem 0 1rem 0;
    }

    .main_content.page-content-wrapper .page-body h4,
    .main_content.page-content-wrapper .page-body h5,
    .main_content.page-content-wrapper .page-body h6 {
        font-weight: 600;
        margin: 1.5rem 0 0.75rem 0;
    }

    .main_content.page-content-wrapper .page-body ul,
    .main_content.page-content-wrapper .page-body ol {
        margin: 1rem 0;
        padding-left: 2rem;
    }

    .main_content.page-content-wrapper .page-body li {
        margin: 0.5rem 0;
    }

    .main_content.page-content-wrapper .page-body a {
        color: #0369a1;
        text-decoration: underline;
    }

    .main_content.page-content-wrapper .page-body a:hover {
        color: #075985;
    }

    .main_content.page-content-wrapper .page-body img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5rem 0;
    }

    .main_content.page-content-wrapper .page-body blockquote {
        border-left: 4px solid #e5e7eb;
        padding: 1rem 1.5rem;
        margin: 1.5rem 0;
        background: #f9fafb;
        font-style: italic;
        color: #4b5563;
    }

    .main_content.page-content-wrapper .page-body code {
        background: #f3f4f6;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-size: 0.9em;
        font-family: 'Courier New', monospace;
    }

    .main_content.page-content-wrapper .page-body pre {
        background: #1f2937;
        color: #f3f4f6;
        padding: 1rem;
        border-radius: 8px;
        overflow-x: auto;
        margin: 1.5rem 0;
    }

    .main_content.page-content-wrapper .page-body pre code {
        background: none;
        padding: 0;
        color: inherit;
    }

    .main_content.page-content-wrapper .page-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
    }

    .main_content.page-content-wrapper .page-body table th,
    .main_content.page-content-wrapper .page-body table td {
        border: 1px solid #e5e7eb;
        padding: 0.75rem;
        text-align: left;
    }

    .main_content.page-content-wrapper .page-body table th {
        background: #f9fafb;
        font-weight: 600;
    }

    .page-meta {
        display: flex;
        gap: 1rem;
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    .page-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .page-meta-item svg {
        width: 1rem;
        height: 1rem;
    }
</style>
@endpush

@section('content')
<div class="content print" role="main">
    <!-- Title Banner with Sidebar -->
    <div class="title_banner with_sidebar">
        <div class="header-blurb container">
            <div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
                <span property="itemListElement" typeof="ListItem">
                    <span class="main-home" property="name">National Vernacular Language Arts (VLA) curriculum</span>
                    <meta property="position" content="1">
                </span>
                @if($page->parent)
                <span> &gt; </span>
                <span property="itemListElement" typeof="ListItem">
                    <a property="item" typeof="WebPage" href="{{ route('pages.show', $page->parent->slug) }}">
                        <span property="name">{{ $page->parent->title }}</span>
                    </a>
                    <meta property="position" content="2">
                </span>
                @endif
            </div>
            <div class="handbook-header-menu">
                <h1>{{ $page->title }}</h1>
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

    <!-- Main Content with Sidebar Layout -->
    <div class="page-content">
        <div class="container with_sidebar">
            <!-- Main Content Area -->
            <div class="main_content page-content-wrapper">
                <!-- Preview Banner (if in preview mode) -->
                @if(isset($isPreview) && $isPreview)
                <div class="preview-banner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" style="width: 1.25rem; height: 1.25rem;">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Preview Mode - This page may not be published yet
                </div>
                @endif

                <!-- Page Meta -->
                @if($page->published_at || ($page->updated_at && $page->updated_at->ne($page->created_at)))
                <div class="page-meta">
                    @if($page->published_at)
                    <span class="page-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                        </svg>
                        Published {{ $page->published_at->format('F j, Y') }}
                    </span>
                    @endif
                    @if($page->updated_at && $page->updated_at->ne($page->created_at))
                    <span class="page-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Updated {{ $page->updated_at->format('F j, Y') }}
                    </span>
                    @endif
                </div>
                @endif

                <!-- Page Content with Anchors -->
                <article class="page-body">
                    {!! $contentWithAnchors !!}
                </article>
            </div>

            <!-- Right Sidebar with TOC -->
            <aside class="sidebar sidebar-links">
                <!-- Table of Contents -->
                <div class="sidebar_item sidebar-menu">
                    @if(count($tableOfContents) > 0)
                    <h2>Sections</h2>
                    <ul class="section-list">
                        @foreach($tableOfContents as $section)
                        <li>
                            <a class="section-anchor" href="{{ $section['url'] }}">{{ $section['heading'] }}</a>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>

                <!-- Ready to Explore -->
                <div class="sidebar_item text-sidebar">
                    <h2>Ready to Explore?</h2>
                    <p>Have you reviewed this material and agreed to terms of use?</p>
                    <p><a class="button-line-color button-line library-entry-sidebar-btn" href="{{ route('library.index') }}"><strong>Access the Digital Library</strong></a></p>
                </div>

                <!-- Resource Contributors -->
                @if($page->resourceContributors->count() > 0)
                <div class="sidebar_item text-sidebar">
                    <h2>Resource Contributors</h2>
                    <p style="text-align: left;">@foreach($page->resourceContributors as $contributor)@if($contributor->website_url)<a href="{{ $contributor->website_url }}" target="_blank" rel="noopener">{{ $contributor->name }}<br/>
                            </a>@else{{ $contributor->name }}<br/>
@endif
@endforeach</p>

                </div>
                @endif
            </aside>
        </div>
    </div>
</div>
@endsection

