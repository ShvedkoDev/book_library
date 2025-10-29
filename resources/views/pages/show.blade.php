@extends('layouts.library')

@section('title', $page->title . ' - Micronesian Teachers Digital Library')
@section('description', $page->meta_description ?? $page->excerpt)
@section('og_type', 'article')

@push('styles')
<style>
    .page-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .page-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 2rem;
        align-items: start;
    }

    .page-content {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .page-header {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 0.5rem 0;
        line-height: 1.2;
    }

    .page-meta {
        display: flex;
        gap: 1rem;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .page-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

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

    /* Content Typography */
    .page-body {
        line-height: 1.8;
        color: #374151;
    }

    .page-body h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin: 2.5rem 0 1rem 0;
        padding-top: 1rem;
        scroll-margin-top: 2rem;
    }

    .page-body h3 {
        font-size: 1.4rem;
        font-weight: 600;
        color: #1f2937;
        margin: 2rem 0 1rem 0;
    }

    .page-body h4, .page-body h5, .page-body h6 {
        font-weight: 600;
        color: #1f2937;
        margin: 1.5rem 0 0.75rem 0;
    }

    .page-body p {
        margin: 1rem 0;
    }

    .page-body ul, .page-body ol {
        margin: 1rem 0;
        padding-left: 2rem;
    }

    .page-body li {
        margin: 0.5rem 0;
    }

    .page-body a {
        color: #0369a1;
        text-decoration: underline;
    }

    .page-body a:hover {
        color: #075985;
    }

    .page-body img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5rem 0;
    }

    .page-body blockquote {
        border-left: 4px solid #e5e7eb;
        padding: 1rem 1.5rem;
        margin: 1.5rem 0;
        background: #f9fafb;
        font-style: italic;
        color: #4b5563;
    }

    .page-body code {
        background: #f3f4f6;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-size: 0.9em;
        font-family: 'Courier New', monospace;
    }

    .page-body pre {
        background: #1f2937;
        color: #f3f4f6;
        padding: 1rem;
        border-radius: 8px;
        overflow-x: auto;
        margin: 1.5rem 0;
    }

    .page-body pre code {
        background: none;
        padding: 0;
        color: inherit;
    }

    .page-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
    }

    .page-body table th,
    .page-body table td {
        border: 1px solid #e5e7eb;
        padding: 0.75rem;
        text-align: left;
    }

    .page-body table th {
        background: #f9fafb;
        font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .page-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .page-container {
            padding: 1rem;
        }

        .page-content {
            padding: 1.5rem;
        }

        .page-title {
            font-size: 2rem;
        }
    }
</style>
@endpush

<div class="page-container">
    <div class="page-layout">
        <!-- Table of Contents Sidebar -->
        <x-page-toc :sections="$tableOfContents" />

        <!-- Main Content -->
        <main class="page-content">
            <!-- Preview Banner (if in preview mode) -->
            @if(isset($isPreview) && $isPreview)
            <div class="preview-banner">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Preview Mode - This page may not be published yet
            </div>
            @endif

            <!-- Page Header -->
            <header class="page-header">
                <h1 class="page-title">{{ $page->title }}</h1>
                <div class="page-meta">
                    @if($page->published_at)
                    <span class="page-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                        </svg>
                        Published {{ $page->published_at->format('F j, Y') }}
                    </span>
                    @endif
                    @if($page->updated_at && $page->updated_at->ne($page->created_at))
                    <span class="page-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Updated {{ $page->updated_at->format('F j, Y') }}
                    </span>
                    @endif
                </div>
            </header>

            <!-- Page Content with Anchors -->
            <article class="page-body">
                {!! $contentWithAnchors !!}
            </article>

            <!-- Resource Contributors Section -->
            <x-resource-contributors :contributors="$page->resourceContributors" />
        </main>
    </div>
</div>

