@extends('layouts.library')

@section('title', $book->title . ' - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('description', Str::limit($book->description ?? 'Educational resource for Micronesian teachers', 160))
@section('og_type', 'book')
@section('og_image', $book->getThumbnailUrl())

@push('styles')
<style>
    /* CSS Variables for colors and common values */
    :root {
        /* Primary colors */
        --color-primary: #1d496a;
        --color-primary-dark: #005a8a;

        /* Status colors */
        --color-success: #155724;
        --color-success-bg: #d4edda;
        --color-success-border: #c3e6cb;

        --color-info: #0c5460;
        --color-info-bg: #d1ecf1;
        --color-info-border: #bee5eb;

        --color-warning: #856404;
        --color-warning-bg: #fff3cd;
        --color-warning-border: #ffc107;

        --color-danger: #721c24;
        --color-danger-bg: #f8d7da;
        --color-danger-border: #f5c6cb;

        --color-approved: #28a745;
        --color-rejected: #dc3545;

        /* Text colors */
        --color-text-primary: #333;
        --color-text-secondary: #666;
        --color-text-muted: #999;
        --color-text-light: #555;

        /* Background colors */
        --color-bg-white: #ffffff;
        --color-bg-light: #f8f9fa;
        --color-bg-gray: #f9f9f9;
        --color-bg-light-gray: #f0f0f0;
        --color-bg-secondary: #e9ecef;

        /* Border colors */
        --color-border: #ddd;
        --color-border-light: #e0e0e0;
        --color-border-dark: #ccc;

        /* Rating colors */
        --color-star: #ffc107;
        --color-star-empty: #ddd;

        /* Spacing */
        --spacing-xs: 0.25rem;
        --spacing-sm: 0.5rem;
        --spacing-md: 0.75rem;
        --spacing-lg: 1rem;
        --spacing-xl: 1.5rem;
        --spacing-2xl: 2rem;
        --spacing-3xl: 3rem;

        /* Border radius */
        --radius-sm: 3px;
        --radius-md: 4px;
        --radius-lg: 6px;
        --radius-xl: 8px;
        --radius-pill: 1rem;

        /* Font sizes */
        --font-xs: 0.65rem;
        --font-sm: 0.75rem;
        --font-base: 0.875rem;
        --font-md: 0.9rem;
        --font-lg: 1rem;
        --font-xl: 1.1rem;
        --font-2xl: 1.2rem;
        --font-3xl: 1.5rem;
        --font-4xl: 2rem;
        --font-5xl: 3rem;

        /* Shadows */
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
        --shadow-md: 0 2px 4px rgba(0,0,0,0.1);

        /* Transitions */
        --transition-fast: 0.2s;
        --transition-normal: 0.3s;
    }

    /* Main content container - max 1020px */
    .library-book-detail {
        max-width: 1020px;
        margin: 0 auto;
        padding: 0 var(--spacing-lg);
    }

    .book-page-container {
        display: grid;
        grid-template-columns: 180px 1fr;
        gap: 2rem;
        padding: 2rem 0;
    }

    .book-cover-section {
        top: 2rem;
        height: fit-content;
    }

    .book-cover-section .book-cover {
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin-bottom: 0.25rem;
        cursor: pointer;
        transition: transform 0.1s ease, box-shadow 0.3s ease;
        transform-style: preserve-3d;
    }

    .book-cover-section .book-cover:hover {
        box-shadow: 0 12px 32px rgba(0,0,0,0.3);
    }

    .access-status {
        padding: 0.5rem;
        text-align: center;
        margin-bottom: 1rem;
        font-weight: normal;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .access-status.full-access {
        color: #155724;
    }

    .access-status.limited-access {
        color: #856404;
    }

    .access-status.unavailable {
        color: #8198b2;
    }

    .access-status svg,
    .access-status i {
        font-size: 1.1rem;
    }

    .book-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .book-action-btn {
        padding: 0 20px;
        border: none;
        border-radius: 22px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
        text-align: center;
        width: 100%;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 14px;
        line-height: 1.2;
        font-family: var(--wp--preset--font-family--proxima-nova);
        white-space: nowrap;
        box-sizing: border-box;
        height: 34px;
        min-height: 34px;
    }

    .book-action-btn.btn-primary {
        background-color: #1d496a;
        color: white;
    }

    .book-action-btn.btn-primary:hover {
        background-color: #1d496a;
    }

    .book-action-btn.btn-secondary {
        background-color: #f0f0f0;
        color: #666;
    }

    .book-action-btn.btn-secondary:hover {
        background-color: #e0e0e0;
    }

    .book-action-btn.btn-action {
        background-color: #fdf4d1;
        color: #333;
        border: 1px solid #ddd;
    }

    .book-action-btn.btn-action:hover {
        background-color: #e9ecef;
    }

    .book-action-btn i,
    .book-action-btn svg {
        font-size: 14px;
        line-height: 1;
    }

    /* Override divider-top for star rating */
    .star-rating-wrapper.divider-top {
        margin-top: 0.5rem;
        padding-top: 0;
        border-top: none;
    }

    /* Star Rating Row */
    .star-rating-row {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.1rem;
        margin: 0.5rem auto;
        flex-wrap: nowrap; /* Prevent wrapping to new line */
        width: 100%;
    }

    .star-rating-row input+button,
    .star-rating-row input+input {
        margin-top: 0; /* Override global form spacing */
    }

    .star-rating-inline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.15rem;
        flex-wrap: nowrap;
        white-space: nowrap;
        max-width: 100%;
    }

    .star-rating-row .star-btn {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #ddd !important; /* Force grey by default */
        font-size: clamp(0.96rem, 3.2vw, 1.36rem);
        transition: color 0.2s ease;
        line-height: 1;
        text-decoration: none !important;
        flex-shrink: 0; /* Prevent stars from shrinking */
    }

    /* Yellow stars only on hover for authenticated users or when active (rated) */
    .star-rating-row .star-btn:hover,
    .star-rating-row .star-btn.active {
        color: #ffc107 !important; /* Yellow for rated/hover */
    }

    /* Ensure anchor tag stars (non-logged-in) are ALWAYS grey, never blue */
    .star-rating-row a.star-btn {
        color: #ddd !important; /* Force grey */
        text-decoration: none !important;
        cursor: default; /* Show this is not directly clickable */
    }

    .star-rating-row a.star-btn:visited,
    .star-rating-row a.star-btn:hover,
    .star-rating-row a.star-btn:active,
    .star-rating-row a.star-btn:focus {
        color: #ddd !important; /* Always grey for non-logged-in users */
    }

    /* Responsive adjustments for very small screens */
    @media (max-width: 400px) {
        .star-rating-row .star-btn {
            font-size: 0.8rem; /* Slightly smaller on very small screens */
            gap: 0.05rem;
        }
    }

    /* Rating helper text */
    .rating-helper-text {
        text-align: center;
        font-size: 0.7rem;
        color: #999;
        margin-top: 0.25rem;
        transition: opacity 0.3s ease;
    }

    /* Action Icons Row */
    .action-icons-row {
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 0.5rem 0;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .action-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        cursor: pointer;
        text-decoration: none !important;
        color: #999 !important; /* Grey by default (for logged out users) - force override link color */
        transition: color 0.2s ease;
        background: none;
        border: none;
        padding: 0;
    }

    .action-icon:hover {
        color: #666 !important; /* Darker grey on hover for logged out users */
    }

    .action-icon:visited {
        color: #999 !important; /* Keep grey for visited links */
    }

    /* When user is logged in, Review and Notes become blue */
    .action-icons-row.authenticated .action-icon {
        color: #1d496a !important; /* Blue for authenticated users */
    }

    .action-icons-row.authenticated .action-icon:hover {
        color: #005a8a !important; /* Darker blue on hover */
    }

    .action-icons-row.authenticated .action-icon:visited {
        color: #1d496a !important; /* Keep blue for visited links when authenticated */
    }

    /* Share button is ALWAYS blue (works for everyone) */
    .action-icon.share-icon {
        color: #1d496a !important;
    }

    .action-icon.share-icon:hover {
        color: #005a8a !important;
    }

    .action-icon i {
        font-size: 1.1rem;
        line-height: 1;
    }

    .action-icon span {
        font-size: 0.65rem;
        font-weight: 500;
        line-height: 1;
    }

    .book-rating {
        border-top: 1px solid #e0e0e0;
        padding-top: 1rem;
    }

    .stars {
        color: #ffc107;
        font-size: 1.4rem;
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

    .collection-link {
        color: #1d496a;
        text-decoration: none;
        font-size: 0.875rem;
    }

    .book-title:not(.related-book-title-block) {
        font-size: 1.8rem;
        margin: 0.5rem 0;
        color: #333;
    }

    h2.book-subtitle {
        font-size: 1rem;
        color: #666;
        margin-top: 0!important;
    }

    .book-author {
        font-size: 1.1rem;
        color: #444;
        margin: 1rem 0;
        display: flex;
        gap: 0.5rem;
    }

    .book-author > div {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .book-author .author-label {
        margin-right: 0.25rem;
    }

    .book-author .author-pill, .details-section .author-pill {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        background-color: #f0f0f0;
        color: #333;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.85rem;
        transition: background-color 0.2s ease;
        cursor: pointer;
    }

    .book-author .author-pill:hover, .details-section .author-pill:hover {
        background-color: #e0e0e0;
        color: #000;
    }

    .book-meta {
        color: #666;
        font-size: 0.875rem;
    }

    .book-meta span {
        margin-right: 0.5rem;
    }

    .book-description p{
        line-height: 1.6;
        font-size: 0.9rem;
    }

    /* Sticky Navigation Bar (OpenLibrary style) */
    .nav-bar-wrapper {
        display: block;
        position: sticky;
        top: 118px;
        background: #ffffff !important;
        background-color: #ffffff !important;
        z-index: 100;
        padding: 0.5rem 1rem 0.5rem 0;
        border-bottom: 1px solid #e8e8e8;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: box-shadow 0.3s ease;
        margin: 0 -1rem 1rem 0;
        opacity: 1 !important;
    }

    .nav-bar-wrapper.scrolled {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        background-color: #ffffff !important;
    }

    /* Edit Info Section (OpenLibrary style) */
    .edit-info-section {
        float: right;
        margin: 0 0 1rem 1.5rem;
        max-width: 280px;
    }

    .edit-info-content {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 0.5rem 0.75rem;
        background: white;
    }

    .edit-meta {
        font-size: 0.65rem;
    }

    .edit-user {
        color: #666;
        margin-bottom: 0.25rem;
        line-height: 1;
    }

    .editor-link {
        color: #1d496a;
        text-decoration: none;
        font-weight: 600;
    }

    .editor-link:hover {
        text-decoration: underline;
    }

    .edit-date {
        font-size: 0.65rem;
        color: #999;
        line-height: 1;
    }

    .history-link {
        color: #1d496a;
        text-decoration: none;
    }

    .history-link:hover {
        text-decoration: underline;
    }

    .edit-button-wrapper {
        text-align: right;
        border-top: 1px solid #e0e0e0;
        padding-top: 0.75rem;
        margin-top: 0.75rem;
    }

    .edit-btn {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        background-color: #f8f9fa;
        color: #333;
        text-decoration: none;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-weight: 600;
        font-size: 0.6rem;
        transition: all 0.2s;
    }

    .edit-btn:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
    }

    /* Clear float after book header */
    .book-header::after {
        content: "";
        display: table;
        clear: both;
    }

    .nav-bar {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        gap: 0.5rem;
    }

    .nav-bar li {
        flex-shrink: 0;
    }

    .nav-bar a {
        display: flex;
        align-items: center;
        padding: 0 1.25rem;
        color: #666;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        border-radius: 20px;
        transition: all 0.2s ease;
        white-space: nowrap;
        background: transparent;
        height: 34px;
        min-height: 34px;
    }

    .nav-bar li.selected a {
        background: #1d496a;
        background-color: #1d496a;
        color: #ffffff !important;
    }

    .nav-bar a:hover:not(.nav-bar li.selected a) {
        background: #f0f0f0;
        background-color: #f0f0f0;
        color: #333;
    }

    .section-anchor {
        display: block;
        position: relative;
        visibility: hidden;
        scroll-margin-top: 190px; /* Account for sticky header (118px) + nav bar (~52px) + padding (20px) */
    }

    .section-anchor--no-height {
        height: 0;
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
        color: #1d496a;
        border-bottom-color: #1d496a;
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

    .detail-item {
        display: flex;
        margin-bottom: 0.75rem;
    }

    .detail-label {
        font-weight: 600;
        min-width: 140px;
        font-size: 0.875rem;
    }

    .detail-value {
        font-size: 0.875rem;
    }

    .related-books {
        margin-top: 3rem;
        width: 100%;
    }

    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(156px, 1fr));
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .books-grid-scroll .book-card {
        flex-shrink: 0;
        width: 156px;
    }

    .book-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.5rem;
        text-align: center;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        height: 100%;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    }

    .book-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    .book-card-cover {
        width: 137px !important;
        height: 206px !important;
        min-width: 137px;
        min-height: 206px;
        max-width: 137px;
        max-height: 206px;
        border-radius: 4px;
        margin: 0 auto 0.75rem auto;
        object-fit: cover;
        display: block;
    }

    .book-card-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.75rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.4;
        min-height: calc(0.75rem * 1.4 * 2);
        text-decoration: none;
    }

    .book-card-author {
        font-size: 0.65rem;
        color: #666;
        margin-bottom: 0.5rem;
        text-decoration: none;
    }

    .book-card-meta {
        font-size: 0.65rem;
        color: #999;
        margin-bottom: 0.75rem;
    }

    .book-card-btn {
        width: 100%;
        padding: 0.5rem;
        background-color: #1d496a;
        color: white!important;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        margin-top: auto;
        display: inline-block;
    }

    /* Subjects Section (OpenLibrary style) */
    .subjects {
        margin: 2rem 0;
        padding: 1.5rem;
        background: #f9f9f9;
        border-radius: 8px;
    }

    .subjects-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .link-box {
        padding: 0;
    }

    .link-box a {
        display: inline-block;
        padding: 0.05rem 0.5rem;
        background-color: #f0f0f0;
        color: #333;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.85rem;
        transition: background-color 0.2s ease;
        cursor: pointer;
        margin-right: 0.25rem;
    }

    .link-box a:hover {
        background-color: #e0e0e0;
        color: #000;
        text-decoration: none;
    }

    .link-box a:last-child:after {
        content: "";
    }

    /* Edition omniline (OpenLibrary style) */
    .edition-omniline {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin: 1.5rem 0;
        padding: 1rem 0;
        border-top: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
    }

    .edition-omniline-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .edition-omniline-item > div:first-child {
        font-size: 0.875rem;
        color: #666;
        font-weight: 600;
    }

    .edition-omniline-item span,
    .edition-omniline-item a {
        font-size: 1rem;
        color: #333;
    }

    .edition-omniline-item a {
        color: #1d496a;
        text-decoration: none;
    }

    .edition-omniline-item a:hover {
        text-decoration: underline;
    }

    /* Read more/less functionality */
    .read-more__content {
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .read-more__toggle {
        background: none;
        border: none;
        color: #1d496a;
        cursor: pointer;
        font-weight: 600;
        padding: 0.5rem 0;
        font-size: 0.95rem;
        float: right;
    }

    .read-more__toggle--less {
        display: none;
    }

    /* Alert and Status Messages */
    .alert {
        padding: var(--spacing-lg);
        border: 1px solid;
        border-radius: var(--radius-lg);
        margin-bottom: var(--spacing-lg);
    }

    .alert-success {
        background: var(--color-success-bg);
        color: var(--color-success);
        border-color: var(--color-success-border);
    }

    .alert-info {
        background: var(--color-info-bg);
        color: var(--color-info);
        border-color: var(--color-info-border);
    }

    .alert-error {
        background: var(--color-danger-bg);
        color: var(--color-danger);
        border-color: var(--color-danger-border);
    }

    .alert ul {
        margin: 0;
        padding-left: var(--spacing-xl);
    }

    .status-box {
        padding: var(--spacing-md);
        border: 1px solid;
        border-radius: var(--radius-lg);
        text-align: center;
    }

    .status-pending {
        background: var(--color-warning-bg);
        border-color: var(--color-warning-border);
    }

    .status-pending strong {
        color: var(--color-warning);
    }

    .status-pending p {
        margin: var(--spacing-sm) 0 0 0;
        font-size: var(--font-base);
        color: var(--color-warning);
    }

    .status-approved {
        background: var(--color-success-bg);
        border-color: var(--color-approved);
    }

    .status-approved strong {
        color: var(--color-success);
    }

    .status-approved p {
        margin: var(--spacing-sm) 0 0 0;
        font-size: var(--font-base);
        color: var(--color-success);
    }

    .status-rejected {
        background: var(--color-danger-bg);
        border-color: var(--color-rejected);
        margin-bottom: var(--spacing-sm);
    }

    .status-rejected strong {
        color: var(--color-danger);
    }

    .status-rejected p {
        margin: var(--spacing-sm) 0 0 0;
        font-size: var(--font-base);
        color: var(--color-danger);
    }

    /* Divider and spacing utilities */
    .divider-top {
        margin-top: var(--spacing-lg);
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--color-border-light);
    }

    .text-link {
        text-decoration: none;
        text-align: center;
    }

    /* Edition info */
    .edition-info {
        font-size: var(--font-md);
        color: var(--color-text-secondary);
        margin-bottom: var(--spacing-sm);
        font-style: italic;
    }

    /* Reader statistics */
    .reader-stats-flex {
        display: flex;
        gap: var(--spacing-2xl);
        margin: var(--spacing-lg) 0;
        flex-wrap: wrap;
    }

    .reader-stat-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .stat-text {
        color: var(--color-text-secondary);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .stat-text strong {
        font-weight: 600;
    }

    .stat-text i {
        color: var(--color-text-muted);
        font-size: 0.9em;
    }

    /* Keyword badges */
    .keyword-badge {
        display: inline-block;
        background: var(--color-bg-secondary);
        padding: var(--spacing-xs) var(--spacing-md);
        margin: var(--spacing-xs);
        border-radius: var(--radius-pill);
        font-size: var(--font-base);
    }

    /* Section containers */
    .section-wrapper {
        margin: var(--spacing-2xl) 0;
    }

    .section-wrapper-small {
        margin-bottom: var(--spacing-xl);
    }

    .section-text {
        line-height: 1.6;
        color: var(--color-text-light);
    }

    .section-text-wide {
        line-height: 1.8;
        color: var(--color-text-light);
    }

    /* Tab sections and headers */
    .tab-section {
        margin: var(--spacing-3xl) 0;
    }

    .tab-section p {
        font-size: 0.8125rem;
    }

    h2.section-title {
        margin-bottom: var(--spacing-sm);
        color: var(--color-text-primary);
        font-size: 1.25rem;
    }

    .section-separator {
        border: none;
        border-top: 1px solid var(--color-border-light);
        margin-bottom: var(--spacing-md);
    }

    /* Library Locations List (compact) */
    .library-locations-list {
        list-style: none;
        padding: 0;
        margin: 0!important;
        max-width: 600px;
    }

    .library-location-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }

    .library-location-square {
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #999;
        border-radius: 3px;
        background: #f0f0f0;
        color: #555;
        font-size: 0.7rem;
        line-height: 1;
    }

    .library-location-square.with-link a {
        color: #1d496a;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .library-location-square.no-link i {
        color: #555;
    }

    .library-location-text {
        font-size: 0.825rem;
        color: #333;
    }

    @media (max-width: 768px) {
        .library-locations-list { max-width: 100%; }
    }

    .related-books-full-width {
        width: 100%;
        margin-top: var(--spacing-3xl);
    }

    .related-books-section {
        margin-top: var(--spacing-2xl);
    }

    .related-books-subsection {
        margin-bottom: var(--spacing-2xl);
    }

    .related-books-table-wrapper {
        overflow-x: auto;
    }

    .related-books-table-wrapper .books-table {
        width: 100%;
        margin-bottom: 0;
    }

    /* Book Info Cards (OpenLibrary style) */
    .book-info-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.75rem;
        margin-bottom: var(--spacing-lg);
        clear: both;
        margin-top: 1rem;
    }

    .info-card {
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 0.625rem;
        background: var(--color-bg-white);
        text-align: center;
    }

    .info-card-label {
        display: block;
        font-size: 0.75rem;
        color: var(--color-text-secondary);
        margin-bottom: 0.25rem;
        font-weight: 500;
    }

    .info-card-value {
        display: block;
        font-size: 0.875rem;
        color: var(--color-primary);
        font-weight: 600;
        line-height: 1.3;
    }

    .info-card-value.publisher {
        font-size: 0.75rem;
    }

    @media (max-width: 640px) {
        .book-info-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Book Details Two-Column Layout (OpenLibrary style) */
    .details-section {
        margin-bottom: var(--spacing-lg);
    }

    .details-subsection {
        margin-bottom: var(--spacing-md);
        padding-bottom: var(--spacing-sm);
    }

    h3.details-subsection-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--color-text-primary);
        margin-bottom: 0.375rem;
        line-height: 1.3;
    }

    .details-row {
        display: grid;
        grid-template-columns: 130px 1fr;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
        align-items: start;
        line-height: 1.5;
    }

    .details-label {
        color: var(--color-text-secondary);
        font-size: 0.8125rem;
        font-weight: 400;
    }

    .details-value {
        color: var(--color-text-primary);
        font-size: 0.8125rem;
    }

    .details-value a {
        color: var(--color-primary);
        text-decoration: none;
    }

    .details-value a:hover {
        text-decoration: underline;
    }

    @media (max-width: 640px) {
        .details-row {
            grid-template-columns: 1fr;
            gap: 0.125rem;
        }

        .details-label {
            font-weight: 600;
        }
    }

    /* Reviews Section */
    .reviews-section {
        margin-top: var(--spacing-xl);
        padding: var(--spacing-lg);
        background: var(--color-bg-gray);
        border-radius: var(--radius-md);
    }

    .reviews-section h2 {
        margin-bottom: var(--spacing-sm);
        color: var(--color-text-primary);
        font-size: 1.25rem;
    }

    .rating-histogram,
    .user-rating-form,
    .user-review-form,
    .review-item,
    .review-guest-message {
        margin-bottom: var(--spacing-md);
        padding: var(--spacing-md);
        background: var(--color-bg-white);
        border-radius: var(--radius-md);
    }

    .rating-histogram h3,
    .user-rating-form h3,
    .user-review-form h3,
    .existing-reviews h3 {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.375rem;
        color: var(--color-text-primary);
    }

    .rating-center {
        display: flex;
        gap: var(--spacing-lg);
        align-items: center;
    }

    .rating-score-display {
        text-align: center;
    }

    .rating-score-number {
        font-size: var(--font-3xl);
        font-weight: bold;
        color: var(--color-primary);
    }

    .rating-score-stars {
        font-size: var(--font-xl);
    }

    .rating-score-count {
        color: var(--color-text-secondary);
        margin-top: var(--spacing-xs);
        font-size: var(--font-sm);
    }

    .rating-bars {
        flex: 1;
    }

    .rating-bar-row {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-xs);
    }

    .rating-bar-label {
        min-width: 60px;
        color: var(--color-text-secondary);
    }

    .rating-bar-container {
        flex: 1;
        height: 20px;
        background: var(--color-border-light);
        border-radius: var(--radius-xl);
        overflow: hidden;
    }

    .rating-bar-fill {
        height: 100%;
        background: var(--color-star);
        transition: width var(--transition-normal);
    }

    .rating-bar-count {
        min-width: 60px;
        text-align: right;
        color: var(--color-text-secondary);
    }

    .rating-empty-state {
        color: var(--color-text-secondary);
        text-align: center;
        padding: var(--spacing-md);
        font-size: 0.875rem;
    }

    /* Rating Form */
    .star-rating-form {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }

    .star-rating {
        display: flex;
        gap: var(--spacing-xs);
    }

    .star-rating label {
        cursor: pointer;
        font-size: var(--font-2xl);
    }

    .rating-text {
        color: var(--color-text-secondary);
        font-size: var(--font-base);
    }

    /* Review Form */
    .review-form-field {
        width: 100%;
        padding: var(--spacing-md);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        resize: vertical;
        font-family: inherit;
        font-size: var(--font-base);
    }

    .review-form-footer {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-top: var(--spacing-md);
    }

    .review-form-note {
        color: var(--color-text-muted);
        font-size: var(--font-xs);
        position: relative;
        top: -15px;
    }

    .btn-submit {
        padding: 0.4rem var(--spacing-lg);
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        font-weight: 600;
        font-size: var(--font-base);
    }

    /* Review Item */
    .review-item {
        padding: var(--spacing-md);
        background: var(--color-bg-white);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-md);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: var(--spacing-xs);
    }

    .review-author {
        font-weight: bold;
        color: var(--color-text-primary);
        font-size: var(--font-base);
    }

    .review-rating {
        display: inline-block;
        margin-left: var(--spacing-md);
        font-size: var(--font-md);
    }

    .review-date {
        color: var(--color-text-muted);
        font-size: var(--font-xs);
    }

    .review-text {
        color: var(--color-text-light);
        line-height: 1.5;
        margin: 0;
        font-size: var(--font-base);
    }

    .guest-message {
        text-align: center;
    }

    .guest-message p {
        color: var(--color-text-secondary);
        margin-bottom: var(--spacing-sm);
        font-size: var(--font-base);
    }

    .guest-message a {
        color: var(--color-primary);
        text-decoration: underline;
    }

    /* Notes Section */
    .notes-section {
        margin-top: var(--spacing-xl);
        padding: var(--spacing-lg);
        background: var(--color-bg-gray);
        border-radius: var(--radius-md);
    }

    .notes-section h3 {
        margin-bottom: var(--spacing-sm);
        color: var(--color-text-primary);
        font-size: 0.825rem;
    }

    .notes-section h2 span {
        font-size: var(--font-sm);
        color: var(--color-text-secondary);
        font-weight: 400;
    }

    .add-note-form,
    .note-item {
        margin-bottom: var(--spacing-lg);
        padding: var(--spacing-md);
        background: var(--color-bg-white);
        border-radius: var(--radius-md);
    }

    .add-note-form h3,
    .existing-notes h3 {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.375rem;
        color: var(--color-text-primary);
    }

    .note-field-label {
        display: block;
        margin-bottom: var(--spacing-xs);
        font-weight: 600;
        color: var(--color-text-primary);
        font-size: var(--font-base);
    }

    .note-field-input {
        width: 100%;
        padding: var(--spacing-sm);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        resize: vertical;
        font-family: inherit;
        font-size: var(--font-base);
    }

    .note-field-small {
        font-size: var(--font-xs);
        color: var(--color-text-secondary);
    }

    .note-field-page-input {
        width: 150px;
        padding: var(--spacing-sm);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        font-family: inherit;
        font-size: var(--font-base);
    }

    .note-field-margin {
        margin-bottom: var(--spacing-md);
    }

    .btn-add-note {
        padding: 0.4rem var(--spacing-lg);
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        font-weight: 600;
        font-size: var(--font-base);
        transition: background var(--transition-normal);
    }

    .note-item {
        border-left: 4px solid var(--color-primary);
    }

    .note-item-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: var(--spacing-sm);
    }

    .note-item-header > div:first-child {
        flex: 1;
    }

    .note-page-badge {
        display: inline-block;
        background: var(--color-bg-light-gray);
        color: var(--color-text-secondary);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius-sm);
        font-size: var(--font-xs);
        margin-bottom: var(--spacing-xs);
    }

    .note-date {
        color: var(--color-text-muted);
        font-size: var(--font-xs);
    }

    .note-actions {
        display: flex;
        gap: var(--spacing-xs);
    }

    .btn-note-edit,
    .btn-note-delete {
        background: none;
        padding: 0.4rem var(--spacing-sm);
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-size: var(--font-xs);
    }

    .btn-note-edit {
        border: 1px solid var(--color-primary);
        color: var(--color-primary);
    }

    .btn-note-delete {
        border: 1px solid var(--color-rejected);
        color: var(--color-rejected);
    }

    .note-content {
        color: var(--color-text-primary);
        line-height: 1.5;
        white-space: pre-wrap;
        font-size: var(--font-base);
    }

    .note-edit-form {
        display: none;
        margin-top: var(--spacing-sm);
    }

    .note-edit-form textarea {
        width: 100%;
        padding: var(--spacing-sm);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        resize: vertical;
        font-family: inherit;
        margin-bottom: var(--spacing-xs);
        font-size: var(--font-base);
    }

    .note-edit-actions {
        display: flex;
        gap: var(--spacing-xs);
    }

    .btn-note-save {
        padding: 0.4rem var(--spacing-md);
        background: var(--color-approved);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-size: var(--font-xs);
    }

    .btn-note-cancel {
        padding: 0.4rem var(--spacing-md);
        background: #6c757d;
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-size: var(--font-xs);
    }

    .notes-empty-state {
        padding: var(--spacing-md);
        background: var(--color-bg-white);
        border-radius: var(--radius-md);
        text-align: center;
    }

    .notes-empty-state p {
        color: var(--color-text-muted);
        font-size: var(--font-base);
    }

    .notes-guest-section {
        margin-top: var(--spacing-xl);
        padding: var(--spacing-lg);
        background: var(--color-bg-light);
        border-radius: var(--radius-md);
        text-align: center;
    }

    .notes-guest-section h2 {
        margin-bottom: var(--spacing-md);
        color: var(--color-text-primary);
        font-size: var(--font-xl);
    }

    .notes-guest-section p {
        color: var(--color-text-secondary);
        margin-bottom: var(--spacing-md);
        font-size: var(--font-base);
    }

    /* Access Request Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: var(--color-bg-white);
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        background: #005a70 !important;
        border-bottom: 3px solid #004556;
    }

    .modal-header h2 {
        margin: 0;
        color: white !important;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .modal-close {
        background: rgba(255,255,255,0.1) !important;
        border: none;
        font-size: 1.75rem;
        cursor: pointer;
        color: white !important;
        width: 32px;
        height: 32px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
        line-height: 1;
        padding: 0;
    }

    .modal-close:hover {
        background: rgba(255,255,255,0.2) !important;
    }

    .modal-description {
        color: var(--color-text-secondary);
        margin-bottom: 1.5rem;
        font-size: 0.9375rem;
        line-height: 1.6;
    }

    .modal-content form {
        padding: 1.5rem;
    }

    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--color-border);
    }

    .btn-modal-cancel {
        padding: 0.625rem 1.5rem;
        background: #f0f0f0;
        color: #333;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9375rem;
        transition: background 0.2s;
    }

    .btn-modal-cancel:hover {
        background: #e0e0e0;
    }

    .btn-modal-submit {
        padding: 0.625rem 1.5rem;
        background: #005a70;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9375rem;
        transition: background 0.2s;
    }

    .btn-modal-submit:hover {
        background: #004556;
    }

    /* Share Modal Specific Styles */
    .share-section {
        margin-bottom: 1.5rem;
    }

    .share-label {
        display: block;
        font-weight: 600;
        font-size: 0.9375rem;
        color: var(--color-text-primary);
        margin-bottom: 0.5rem;
    }

    .btn-copy-link {
        width: 100%;
        padding: 0.875rem 1.5rem;
        background: #005a70;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 1rem;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.625rem;
        margin-bottom: 0.5rem;
    }

    .btn-copy-link:hover {
        background: #004556;
    }

    .btn-copy-link i {
        font-size: 1.125rem;
    }

    .copy-feedback {
        font-size: 0.875rem;
        color: #28a745;
        font-weight: 600;
        min-height: 1.25rem;
        padding-left: 0.25rem;
    }

    .qr-code-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 1.5rem;
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        margin-bottom: 0.75rem;
    }

    .qr-code-container canvas {
        display: block;
    }

    .qr-help-text {
        text-align: center;
        font-size: 0.875rem;
        color: var(--color-text-secondary);
        margin: 0;
    }

    @media (max-width: 768px) {
        .book-page-container {
            grid-template-columns: 1fr;
        }

        .book-cover-section {
            position: static;
        }

        .edit-info-section {
            float: none;
            margin: 1rem 0;
            max-width: 100%;
        }

        .nav-bar-wrapper {
            margin: 2rem 0 0 0;
        }
    }

    /* Lightbox Modal for Book Cover */
    .cover-lightbox {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        animation: fadeIn 0.3s ease;
    }

    .cover-lightbox.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lightbox-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        animation: zoomIn 0.3s ease;
    }

    .lightbox-content img {
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 90vh;
        border-radius: 8px;
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.5);
    }

    .lightbox-close {
        position: absolute;
        top: -40px;
        right: 0;
        color: #fff;
        font-size: 36px;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        width: 40px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        transition: transform 0.2s ease;
    }

    .lightbox-close:hover {
        transform: scale(1.2);
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes zoomIn {
        from {
            transform: scale(0.8);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Collapsible sections styles */
    .collapsible-section {
        margin-top: 2rem;
    }

    .collapsible-header {
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: color 0.2s ease;
    }

    .collapsible-header:hover {
        color: var(--color-primary);
    }

    .collapsible-header .toggle-icon {
        font-size: 0.9rem;
        transition: transform 0.2s ease;
        color: var(--color-text-secondary);
    }

    .collapsible-header.expanded .toggle-icon {
        transform: rotate(90deg);
    }

    .collapsible-content {
        max-height: 5000px;
        overflow: hidden;
        transition: max-height 0.3s ease, opacity 0.3s ease;
        opacity: 1;
    }

    .collapsible-content.collapsed {
        max-height: 0;
        opacity: 0;
    }
</style>
@endpush

@section('content')
<div class="container library-book-detail" data-book-id="{{ $book->id }}">
    <!-- Success Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="book-page-container">
        <!-- Book Cover Section -->
        <div class="book-cover-section">
            @php
                // Try to get primary PDF first, then fall back to any PDF
                $pdfFile = $book->files->where('file_type', 'pdf')->where('is_primary', true)->first();
                if (!$pdfFile) {
                    $pdfFile = $book->files->where('file_type', 'pdf')->first();
                }
            @endphp

            <img src="{{ $book->getThumbnailUrl() }}" alt="{{ $book->title }}" class="book-cover" onclick="openCoverLightbox()" title="Click to view larger image">

            <div class="access-status {{ $book->access_level === 'full' ? 'full-access' : ($book->access_level === 'limited' ? 'limited-access' : 'unavailable') }}">
                @if($book->access_level === 'full')
                    <i class="fal fa-book-open"></i>
                    <span>Full access</span>
                @elseif($book->access_level === 'limited')
                    <i class="fal fa-lock"></i>
                    <span>Limited access</span>
                @else
                    <i class="fal fa-ban"></i>
                    <span>Unavailable</span>
                @endif
            </div>

            <div class="book-actions">
                @if($book->access_level === 'full' && $pdfFile)
                    <!-- View PDF - Always accessible, always blue -->
                    <a href="{{ route('library.view-pdf', ['book' => $book->id, 'file' => $pdfFile->id]) }}" target="_blank" class="book-action-btn btn-primary">
                        <i class="fal fa-eye"></i> View PDF
                    </a>
                    <!-- Download PDF - Blue when logged in, grey when not -->
                    @auth
                        <a href="{{ route('library.download', ['book' => $book->id, 'file' => $pdfFile->id]) }}" class="book-action-btn btn-primary">
                            <i class="fal fa-download"></i> Download PDF
                        </a>
                    @else
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="book-action-btn btn-secondary" title="Please log in to download">
                            <i class="fal fa-download"></i> Download PDF
                        </a>
                    @endauth
                @elseif($book->access_level === 'limited' && $pdfFile)
                    <!-- View PDF - Always accessible, always blue -->
                    <a href="{{ route('library.view-pdf', ['book' => $book->id, 'file' => $pdfFile->id]) }}" target="_blank" class="book-action-btn btn-primary">
                        <i class="fal fa-eye"></i> View PDF
                    </a>
                    @auth
                        @if($userAccessRequest)
                            @if($userAccessRequest->status === 'pending')
                                <div class="status-box status-pending">
                                    <strong>Request pending</strong>
                                    <p>Your access request is being reviewed.</p>
                                </div>
                            @elseif($userAccessRequest->status === 'approved')
                                <div class="status-box status-approved">
                                    <strong>Access approved</strong>
                                    <p>Your request has been approved. Check your email for instructions.</p>
                                </div>
                            @elseif($userAccessRequest->status === 'rejected')
                                <div class="status-box status-rejected">
                                    <strong>Request Rejected</strong>
                                    <p>Your previous request was not approved.</p>
                                </div>
                                <button onclick="openAccessRequestModal()" class="book-action-btn btn-secondary">
                                    Request again
                                </button>
                            @else
                                <button onclick="openAccessRequestModal()" class="book-action-btn btn-secondary">
                                    Request access
                                </button>
                            @endif
                        @else
                            <button onclick="openAccessRequestModal()" class="book-action-btn btn-secondary">
                                Request access
                            </button>
                        @endif
                    @else
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                           class="book-action-btn btn-secondary text-link"
                           title="Request access">
                            Request access
                        </a>
                    @endauth
                @else
                    @auth
                        @if($userAccessRequest)
                            @if($userAccessRequest->status === 'pending')
                                <div class="status-box status-pending">
                                    <strong>Request pending</strong>
                                    <p>Your information request is being reviewed.</p>
                                </div>
                            @elseif($userAccessRequest->status === 'approved')
                                <div class="status-box status-approved">
                                    <strong>Request approved</strong>
                                    <p>Your request has been approved. Check your email for information.</p>
                                </div>
                            @elseif($userAccessRequest->status === 'rejected')
                                <div class="status-box status-rejected">
                                    <strong>Request rejected</strong>
                                    <p>Your previous request was not approved.</p>
                                </div>
                                <button onclick="openAccessRequestModal()" class="book-action-btn btn-secondary">
                                    Request again
                                </button>
                            @else
                                <button onclick="openAccessRequestModal()" class="book-action-btn btn-secondary">
                                    Request information
                                </button>
                            @endif
                        @else
                            <button onclick="openAccessRequestModal()" class="book-action-btn btn-secondary">
                                Request information
                            </button>
                        @endif
                    @else
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                           class="book-action-btn btn-secondary text-link"
                           title="Request information">
                            Request information
                        </a>
                    @endauth
                @endif
            </div>

            <!-- Star Rating Row -->
            <div class="divider-top star-rating-wrapper">
                <div class="star-rating-row">
                    @auth
                        <form action="{{ route('library.rate', $book->id) }}" method="POST" id="quick-rating-form" class="star-rating-inline">
                            @csrf
                            <input type="hidden" name="rating" id="quick-rating-value">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" class="star-btn {{ $userRating && $i <= $userRating->rating ? 'active' : '' }}" data-rating="{{ $i }}" onclick="submitQuickRating({{ $i }})" title="Click to rate {{ $i }} star{{ $i > 1 ? 's' : '' }}{{ $userRating && $i == $userRating->rating ? ' (click again to remove rating)' : '' }}">★</button>
                            @endfor
                        </form>
                    @else
                        <div class="star-rating-inline">
                            @for($i = 1; $i <= 5; $i++)
                                <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="star-btn" title="Please log in to rate">★</a>
                            @endfor
                        </div>
                    @endauth
                </div>
                @auth
                    <div id="rating-helper-text" class="rating-helper-text" style="display: {{ $userRating ? 'block' : 'none' }};">
                        Click rating again to remove
                    </div>
                @endauth
            </div>

            <!-- Action Icons Row -->
            <div>
                <div class="action-icons-row @auth authenticated @endauth">
                    <button onclick="scrollToSection('reader-observations')" class="action-icon">
                        <i class="fal fa-comment"></i>
                        <span>Review</span>
                    </button>
                    @auth
                        <button onclick="scrollToSection('notes-section')" class="action-icon">
                            <i class="fal fa-pen"></i>
                            <span>Notes</span>
                        </button>
                    @else
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="action-icon" title="Please log in to add notes">
                            <i class="fal fa-pen"></i>
                            <span>Notes</span>
                        </a>
                    @endauth
                    <button onclick="openShareModal()" class="action-icon share-icon">
                        <i class="fal fa-share-alt"></i>
                        <span>Share</span>
                    </button>
                </div>
            </div>

            <!-- Bookmark Button (Auth Required) -->
            <div>
                @auth
                    <x-bookmark-button
                        :book="$book"
                        :isBookmarked="$book->isBookmarkedBy(Auth::id())"
                    />
                @else
                    <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="book-action-btn btn-secondary" title="Please log in to bookmark">
                        <i class="fal fa-bookmark"></i> Bookmark
                    </a>
                @endauth
            </div>
        </div>

        <!-- Book Info Section -->
        <div class="book-info-section">
            <!-- Sticky Navigation Bar -->
            <div class="nav-bar-wrapper sticky">
                <ul class="nav-bar work-menu">
                    <li class="selected">
                        <a href="#overview">Overview</a>
                    </li>
                    <li>
                        <a href="#details">Details</a>
                    </li>
                    <li>
                        <a href="#library">Library locations</a>
                    </li>
                    <li>
                        <a href="#reader-observations">User feedback{{ $book->reviews->count() > 0 ? ' (' . $book->reviews->count() . ')' : '' }}</a>
                    </li>
                    @if($hasRelatedBookSections)
                        <li>
                            <a href="#related-books">More books</a>
                        </li>
                    @endif
                </ul>
            </div>

            <a id="overview" name="overview" class="section-anchor section-anchor--no-height"></a>

            <!-- Edit Info Section (OpenLibrary style) - floated right -->
            <div class="edit-info-section">
                <div class="edit-info-content">
                    <div class="edit-meta">
                        <div class="edit-user">
                            Last edited by
                            @if($book->updated_by_user)
                                <a href="#" class="editor-link">{{ $book->updated_by_user->name }}</a>
                            @else
                                <span>System</span>
                            @endif
                        </div>
                        <div class="edit-date">
                            {{ $book->updated_at->format('F j, Y') }}
                            @if($book->id)
                                | <a href="#" class="history-link" title="View edit history">History</a>
                            @endif
                        </div>
                    </div>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <div class="edit-button-wrapper">
                                <a href="{{ route('filament.admin.resources.books.edit', $book) }}"
                                   class="edit-btn"
                                   title="Edit this book"
                                   target="_blank">
                                    Edit
                                </a>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="book-header">
                @if($book->collection && !empty($book->collection->name))
                    <div class="edition-info">
                        An edition from {{ $book->collection->name }}
                        @if($book->publication_year)
                            <span>({{ $book->publication_year }})</span>
                        @endif
                    </div>
                @elseif($book->publication_year)
                    <div class="edition-info">
                        First published in {{ $book->publication_year }}
                    </div>
                @endif
                <h1 class="book-title" @if($book->translated_title) title="{{ $book->translated_title }}" @endif>
                    {{ $book->title }}
                </h1>
                @if($book->subtitle)
                    <h2 class="book-subtitle">{{ $book->subtitle }}</h2>
                @endif
                <div class="book-author">
                    @if($book->creators->isNotEmpty())
                        <span class="author-label">by</span>
                        @foreach($book->creators->unique('id') as $creator)
                            <a href="{{ route('library.index', ['search' => $creator->name]) }}" class="author-pill">{{ $creator->name }}</a>
                        @endforeach
                    @endif
                </div>

                <!-- Reader Stats -->
                <div class="reader-stats-flex">
                    <div class="stat-text">
                        <i class="fal fa-eye"></i> <strong>{{ number_format($book->view_count) }}</strong> {{ Str::plural('view', $book->view_count) }}
                    </div>
                    <div class="stat-text">
                        <i class="fal fa-download"></i> <strong>{{ number_format($book->download_count) }}</strong> {{ Str::plural('download', $book->download_count) }}
                    </div>
                    <div class="stat-text">
                        <i class="fal fa-star"></i>
                        @if($totalRatings > 0)
                            <strong>{{ number_format($averageRating, 1) }}</strong> ({{ $totalRatings }} {{ Str::plural('rating', $totalRatings) }})
                        @else
                            No ratings yet
                        @endif
                    </div>
                    <div class="stat-text">
                        <i class="fal fa-check-circle"></i>
                        @if($book->reviews->count() > 0)
                            <strong>{{ $book->reviews->count() }}</strong> {{ Str::plural('review', $book->reviews->count()) }}
                        @else
                            No reviews yet
                        @endif
                    </div>
                </div>
            </div>

            <!-- Book Description with Read More -->
            @if($book->description)
                <div class="book-description read-more">
                    <div class="read-more__content" id="book-description" style="max-height: 120px;">
                        <p>{{ $book->description }}</p>
                    </div>
                    @if(strlen($book->description) > 300)
                        <button class="read-more__toggle read-more__toggle--more" onclick="toggleReadMore()">Read more ▼</button>
                        <button class="read-more__toggle read-more__toggle--less" onclick="toggleReadMore()">Read less ▲</button>
                    @endif
                </div>
            @endif

            <!-- Info Cards (OpenLibrary style) -->
            <div class="book-info-cards">
                <div class="info-card">
                    <span class="info-card-label">Publication date</span>
                    <span class="info-card-value">{{ $book->publication_year ?? 'N/A' }}</span>
                </div>
                <div class="info-card">
                    <span class="info-card-label">Type</span>
                    <span class="info-card-value">{{ $book->physicalType?->name ?? 'N/A' }}</span>
                </div>
                <div class="info-card">
                    <span class="info-card-label">Language</span>
                    <span class="info-card-value">
                            {{ $book->languages->isNotEmpty() ? $book->languages->pluck('name')->join(', ') : 'N/A' }}
                        </span>
                </div>
                <div class="info-card">
                    <span class="info-card-label">Pages</span>
                    <span class="info-card-value">{{ $book->pages ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Subjects Section -->
            @if($book->purposeClassifications->isNotEmpty() || $book->learnerLevelClassifications->isNotEmpty() || $book->keywords->isNotEmpty() || $book->authors->isNotEmpty() || $book->illustrators->isNotEmpty())
                <div class="">
                    <div class="subjects-content">

                        @if($book->learnerLevelClassifications->isNotEmpty())
                            <div class="link-box">
                                <h3 class="details-subsection-title">Grade levels</h3>
                                @foreach($book->learnerLevelClassifications as $classification)
                                    <p class="details-value">{{ $classification->value }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if($book->keywords && $book->keywords->isNotEmpty())
                            <div class="link-box">
                                <h3 class="details-subsection-title">Keywords</h3>
                                <p class="details-value">
                                    @foreach($book->keywords as $keywordObj)
                                        <a href="{{ route('library.index', ['search' => $keywordObj->keyword]) }}">{{ $keywordObj->keyword }}</a>
                                    @endforeach
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Abstract and Table of Contents -->
            @if($book->abstract || $book->table_of_contents)
                <div class="section-wrapper">
                    @if($book->abstract)
                        <div class="section-wrapper-small">
                            <h3>Abstract</h3>
                            <p class="section-text">{{ $book->abstract }}</p>
                        </div>
                    @endif

                    @if($book->table_of_contents)
                        <div>
                            <h3>Table of contents</h3>
                            <div class="section-text-wide">{!! nl2br(e($book->table_of_contents)) !!}</div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Details Section -->
            <a id="details" name="details" class="section-anchor"></a>
            <div class="tab-section" style="margin-top: 1rem!important;">
                <h2 class="section-title text-left">Book details</h2>
                <hr class="section-separator">

                <div class="details-section">
                    <!-- Contributors -->
                    @if($book->creators->isNotEmpty())
                        @php
                            // Group creators by role (use role_description if available, otherwise creator_type)
                            $creatorsByRole = $book->creators->groupBy(function($creator) {
                                $type = $creator->pivot->creator_type ?? 'other';
                                $role = $creator->pivot->role_description;

                                // For standard types without custom role, use the type
                                if (in_array($type, ['author', 'illustrator', 'editor']) && empty($role)) {
                                    return $type;
                                }

                                // For other types with role description, use the role description
                                if (!empty($role)) {
                                    return 'role:' . $role;
                                }

                                // Default to the type
                                return $type;
                            });
                        @endphp
                        <div class="details-subsection">
                            <h3 class="details-subsection-title">Contributors</h3>

                            @if($creatorsByRole->has('author'))
                                <div class="details-row">
                                    <span class="details-label">Author(s)</span>
                                    <span class="details-value">{{ $creatorsByRole['author']->pluck('name')->join('; ') }}</span>
                                </div>
                            @endif

                            @if($creatorsByRole->has('illustrator'))
                                <div class="details-row">
                                    <span class="details-label">Illustrator(s)</span>
                                    <span class="details-value">{{ $creatorsByRole['illustrator']->pluck('name')->join('; ') }}</span>
                                </div>
                            @endif

                            @if($creatorsByRole->has('editor'))
                                <div class="details-row">
                                    <span class="details-label">Editor(s)</span>
                                    <span class="details-value">{{ $creatorsByRole['editor']->pluck('name')->join('; ') }}</span>
                                </div>
                            @endif

                            @foreach($creatorsByRole as $roleKey => $creators)
                                @if(!in_array($roleKey, ['author', 'illustrator', 'editor']))
                                    @php
                                        // Extract role label from the key
                                        if (str_starts_with($roleKey, 'role:')) {
                                            $roleLabel = ucfirst(substr($roleKey, 5)); // Remove 'role:' prefix and capitalize
                                        } else {
                                            $roleLabel = ucfirst($roleKey);
                                        }
                                    @endphp
                                    <div class="details-row">
                                        <span class="details-label">{{ $roleLabel }}</span>
                                        <span class="details-value">{{ $creators->pluck('name')->join('; ') }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <!-- Edition Notes -->
                    @if($book->publisher)
                        <div class="details-subsection">
                            <h3 class="details-subsection-title">Edition notes</h3>
                            <div class="details-row">
                                <span class="details-label">Publisher</span>
                                <span class="details-value">
                                    <a href="{{ route('library.index', ['search' => $book->publisher->name]) }}" class="author-pill">{{ $book->publisher->name }}</a>
                                </span>
                            </div>
                            @if($book->publisher->program_name)
                                <div class="details-row">
                                    <span class="details-label">Project/partner</span>
                                    <span class="details-value">
                                        <a href="{{ route('library.index', ['search' => $book->publisher->program_name]) }}" class="author-pill">{{ $book->publisher->program_name }}</a>
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Classifications -->
                    @if($book->purposeClassifications->isNotEmpty() || $book->genreClassifications->isNotEmpty() || $book->subgenreClassifications->isNotEmpty() || $book->themesClassifications->isNotEmpty() || $book->typeClassifications->isNotEmpty() || $book->learnerLevelClassifications->isNotEmpty())
                        <div class="details-subsection">
                            <h3 class="details-subsection-title">Classification</h3>
                            @if($book->purposeClassifications->isNotEmpty())
                                <div class="details-row">
                                    <span class="details-label">Purpose</span>
                                    <span class="details-value">
                                        @foreach($book->purposeClassifications as $index => $classification)
                                            <a href="{{ route('library.index', ['subjects' => [$classification->id]]) }}" class="author-pill">{{ $classification->value }}</a>{{ $index < $book->purposeClassifications->count() - 1 ? ', ' : '' }}
                                        @endforeach
                                    </span>
                                </div>
                            @endif
                            @if($book->genreClassifications->isNotEmpty())
                                <div class="details-row">
                                    <span class="details-label">Genre</span>
                                    <span class="details-value">
                                        @foreach($book->genreClassifications as $index => $classification)
                                            <a href="{{ route('library.index', ['genres' => [$classification->id]]) }}" class="author-pill">{{ $classification->value }}</a>{{ $index < $book->genreClassifications->count() - 1 ? ', ' : '' }}
                                        @endforeach
                                    </span>
                                </div>
                            @endif
                            @if($book->subgenreClassifications->isNotEmpty())
                                <div class="details-row">
                                    <span class="details-label">Sub-genre</span>
                                    <span class="details-value">
                                        @foreach($book->subgenreClassifications as $index => $classification)
                                            <a href="{{ route('library.index', ['subgenres' => [$classification->id]]) }}" class="author-pill">{{ $classification->value }}</a>{{ $index < $book->subgenreClassifications->count() - 1 ? ', ' : '' }}
                                        @endforeach
                                    </span>
                                </div>
                            @endif
                            @if($book->themesClassifications->isNotEmpty())
                                <div class="details-row">
                                    <span class="details-label">Subject</span>
                                    <span class="details-value">
                                        @foreach($book->themesClassifications as $index => $classification)
                                            <a href="{{ route('library.index', ['search' => $classification->value]) }}" class="author-pill">{{ $classification->value }}</a>{{ $index < $book->themesClassifications->count() - 1 ? ', ' : '' }}
                                        @endforeach
                                    </span>
                                </div>
                            @endif
                            @if($book->typeClassifications->isNotEmpty())
                                <div class="details-row">
                                    <span class="details-label">Type</span>
                                    <span class="details-value">{{ $book->typeClassifications->pluck('value')->join(', ') }}</span>
                                </div>
                            @endif
                            @if($book->learnerLevelClassifications->isNotEmpty())
                                <div class="details-row">
                                    <span class="details-label">Grade level</span>
                                    <span class="details-value">{{ $book->learnerLevelClassifications->pluck('value')->join(', ') }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Edition Identifiers -->
                    @if($book->isbn_10 || $book->isbn_13 || $book->palm_code || $book->internal_id)
                        <div class="details-subsection">
                            <h3 class="details-subsection-title">Edition identifiers</h3>
                            @if($book->internal_id)
                                <div class="details-row">
                                    <span class="details-label">NVLAC ID</span>
                                    <span class="details-value">{{ $book->internal_id }}</span>
                                </div>
                            @endif
                            @if($book->isbn_10)
                                <div class="details-row">
                                    <span class="details-label">ISBN 10</span>
                                    <span class="details-value">{{ $book->isbn_10 }}</span>
                                </div>
                            @endif
                            @if($book->isbn_13)
                                <div class="details-row">
                                    <span class="details-label">ISBN 13</span>
                                    <span class="details-value">{{ $book->isbn_13 }}</span>
                                </div>
                            @endif
                            @if($book->palm_code)
                                <div class="details-row">
                                    <span class="details-label">PALM code</span>
                                    <span class="details-value">{{ $book->palm_code }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            </div>

            <!-- Library Locations Section -->
            <a id="library" name="library" class="section-anchor"></a>
            <div class="tab-section">
                <h2 class="section-title text-left">Library locations</h2>
                <hr class="section-separator">
                @php
                    // All libraries to display
                    $allLibraries = [
                        'University of Hawaiʻi',
                        'College of Micronesia - FSM',
                        'Micronesian Seminar',
                        'University of Guam'
                    ];

                    // Create a lookup map for existing references
                    $libraryLinksMap = [];
                    foreach($book->libraryReferences as $reference) {
                        $libraryLinksMap[$reference->library_name] = $reference->main_link ?: ($reference->catalog_link ?: $reference->alt_link);
                    }
                @endphp
                <ul class="library-locations-list">
                    @foreach($allLibraries as $libraryName)
                        @php
                            $linkUrl = $libraryLinksMap[$libraryName] ?? null;
                        @endphp
                        <li class="library-location-item">
                            <span class="library-location-square {{ $linkUrl ? 'with-link' : 'no-link' }}">
                                @if($linkUrl)
                                    <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Open library catalog in new tab">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @else
                                    <i class="fas fa-times"></i>
                                @endif
                            </span>
                            <span class="library-location-text">{{ $libraryName }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if($hasAdvancedRelatedBookSections)
                @php
                    $relatedSections = [
                        [
                            'title' => 'Other editions',
                            'books' => $relatedOtherEditions,
                        ],
                        [
                            'title' => 'Other language versions',
                            'books' => $relatedOtherLanguageVersions,
                        ],
                        [
                            'title' => 'Other closely related titles',
                            'books' => $relatedCloselyTitles,
                        ],
                    ];
                @endphp

                @foreach($relatedSections as $section)
                    @if($section['books']->isNotEmpty())
                        <div class="tab-section related-books-section">
                            <h2 class="section-title text-left">{{ $section['title'] }}</h2>
                            <hr class="section-separator">
                            <div class="related-books-subsection">
                                <div class="related-books-table-wrapper">
                                    <table class="books-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px;"></th>
                                                <th>Title/Edition</th>
                                                <th style="width: 120px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($section['books'] as $relatedBook)
                                                @php
                                                    $descriptionParts = [];
                                                    if ($relatedBook->purposeClassifications->isNotEmpty()) {
                                                        $descriptionParts[] = $relatedBook->purposeClassifications->pluck('value')->join(', ');
                                                    }
                                                    if ($relatedBook->learnerLevelClassifications->isNotEmpty()) {
                                                        $descriptionParts[] = $relatedBook->learnerLevelClassifications->pluck('value')->join(', ');
                                                    }
                                                    if ($relatedBook->languages->isNotEmpty()) {
                                                        $descriptionParts[] = $relatedBook->languages->pluck('name')->join(', ');
                                                    }
                                                @endphp
                                                <tr class="book-row">
                                                    <td class="book-cover-cell">
                                                        <img src="{{ $relatedBook->getThumbnailUrl() }}"
                                                             alt="{{ $relatedBook->title }}"
                                                             class="book-cover">
                                                    </td>
                                                    <td class="book-details-cell">
                                                        <div class="book-title related-book-title-block">
                                                            <a target="_blank" href="{{ route('library.show', $relatedBook->slug) }}">
                                                                <span>{{ $relatedBook->title }}</span>
                                                                @if($relatedBook->subtitle)
                                                                    &nbsp;&ndash; <span style="font-weight: normal">{{ $relatedBook->subtitle }}</span>
                                                                @endif
                                                            </a>
                                                        </div>
                                                        <div class="book-metadata">
                                                            {{ $relatedBook->publication_year ?? 'N/A' }}
                                                        </div>
                                                        <div class="book-description">
                                                            {{ implode(', ', array_filter($descriptionParts)) }}
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
                                                            <a href="{{ route('library.show', $relatedBook->slug) }}" class="button button-primary btn-view">Locate</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

            <!-- User feedback (reviews) section -->
            <a id="reader-observations" name="reader-observations" class="section-anchor"></a>
            <div class="tab-section collapsible-section">
                <h2 class="section-title text-left collapsible-header" onclick="toggleCollapsibleSection(this)">
                    <i class="fal fa-chevron-right toggle-icon"></i>
                    User feedback ({{ $book->reviews->count() }} {{ Str::plural('review', $book->reviews->count()) }})
                </h2>
                <div class="collapsible-content collapsed">
                    <hr class="section-separator">
                    <div class="reviews-section">
                        @include('library.partials.reviews')
                    </div>
                </div>
            </div>

            <!-- My notes section -->
            @auth
                <a id="notes-section" name="notes-section" class="section-anchor"></a>
                <div class="tab-section collapsible-section">
                    <h2 class="section-title text-left collapsible-header" onclick="toggleCollapsibleSection(this)">
                        <i class="fal fa-chevron-right toggle-icon"></i>
                        My notes
                    </h2>
                    <div class="collapsible-content collapsed">
                        <hr class="section-separator">
                        <div class="notes-section">
                            @include('library.partials.notes')
                        </div>
                    </div>
                </div>
            @endauth

        </div>
    </div>

    @if($hasRelatedBookSections)
        <a id="related-books" name="related-books" class="section-anchor"></a>
        <div class="related-books-full-width">
            @if($relatedByCreator->isNotEmpty())
                <x-library.related-books-carousel :books="$relatedByCreator" title="More books by the same author" sectionId="related-by-creator" />
            @endif
            @if($relatedByCollection->isNotEmpty())
                <x-library.related-books-carousel :books="$relatedByCollection" title="More books from the same collection" sectionId="related-by-collection" />
            @endif
            @if($relatedByLanguage->isNotEmpty())
                <x-library.related-books-carousel :books="$relatedByLanguage" title="More books in the same language" sectionId="related-by-language" />
            @endif
        </div>
    @endif

    <!-- Access Request Modal -->
    @auth
    <div id="accessRequestModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Request access</h2>
                <button onclick="closeAccessRequestModal()" class="modal-close">&times;</button>
            </div>

            <form action="{{ route('library.request-access', $book->id) }}" method="POST">
                <p class="modal-description">
                    Fill out the form below to request access to <strong>{{ $book->title }}</strong>. We will review your request and contact you via email.
                </p>
                @csrf
                <div class="note-field-margin">
                    <label for="access_request_name" class="note-field-label">Name *</label>
                    <input type="text"
                           id="access_request_name"
                           name="name"
                           value="{{ auth()->user()->name }}"
                           required
                           class="note-field-input">
                </div>

                <div class="note-field-margin">
                    <label for="access_request_email" class="note-field-label">Email *</label>
                    <input type="email"
                           id="access_request_email"
                           name="email"
                           value="{{ auth()->user()->email }}"
                           required
                           class="note-field-input">
                </div>

                <div class="note-field-margin">
                    <label for="access_request_message" class="note-field-label">Message (optional)</label>
                    <textarea id="access_request_message"
                              name="message"
                              rows="4"
                              placeholder="Why do you need access to this book?"
                              class="note-field-input"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button"
                            onclick="closeAccessRequestModal()"
                            class="btn-modal-cancel">
                        Cancel
                    </button>
                    <button type="submit"
                            class="btn-modal-submit">
                        Submit request
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endauth

    <!-- Share Modal -->
    <div id="shareModal" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h2>Share this book</h2>
                <button onclick="closeShareModal()" class="modal-close">&times;</button>
            </div>

            <div style="padding: 1.5rem;">
                <p class="modal-description" style="margin-bottom: 1rem;">
                    Share <strong>{{ $book->title }}</strong> with others
                </p>

                <!-- Copy URL Section -->
                <div class="share-section">
                    <button onclick="copyShareUrl()" class="btn-copy-link">
                        <i class="fal fa-copy"></i> Copy link
                    </button>
                    <div id="copyFeedback" class="copy-feedback"></div>
                </div>

                <!-- QR Code Section -->
                <div class="share-section">
                    <label class="share-label">QR Code</label>
                    <div class="qr-code-container">
                        <div id="qrcode"></div>
                    </div>
                    <p class="qr-help-text">Scan this QR code to open the book page</p>
                </div>

                <div class="modal-actions">
                    <button type="button"
                            onclick="closeShareModal()"
                            class="btn-modal-cancel">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cover Lightbox Modal -->
    <div id="coverLightbox" class="cover-lightbox" onclick="closeCoverLightbox(event)">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeCoverLightbox(event)">&times;</button>
            <img src="{{ $book->getThumbnailUrl() }}" alt="{{ $book->title }}">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- QRCode.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    // Read More/Less functionality
    function toggleReadMore() {
        const content = document.getElementById('book-description');
        const moreBtn = document.querySelector('.read-more__toggle--more');
        const lessBtn = document.querySelector('.read-more__toggle--less');

        if (content.style.maxHeight === '120px' || !content.style.maxHeight) {
            content.style.maxHeight = 'none';
            moreBtn.style.display = 'none';
            lessBtn.style.display = 'block';
        } else {
            content.style.maxHeight = '120px';
            moreBtn.style.display = 'block';
            lessBtn.style.display = 'none';
        }
    }

    // Scroll to section functionality
    function scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            // Calculate offset: header (118px) + nav-bar height (~52px) + padding (20px)
            const headerHeight = 118;
            const navBarHeight = document.querySelector('.nav-bar-wrapper')?.offsetHeight || 52;
            const offset = headerHeight + navBarHeight + 20;

            const elementPosition = section.getBoundingClientRect().top + window.pageYOffset;
            const offsetPosition = elementPosition - offset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    }

    // Sticky Navigation Active State
    document.addEventListener('DOMContentLoaded', function() {
        const sections = document.querySelectorAll('.section-anchor');
        const navLinks = document.querySelectorAll('.nav-bar a');
        const navBarWrapper = document.querySelector('.nav-bar-wrapper');
        let navBarOffset = navBarWrapper ? navBarWrapper.offsetTop : 0;

        // Update active state based on scroll position
        function updateActiveNav() {
            let current = '';
            const scrollOffset = 220; // Header + nav-bar + padding for accurate detection

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (window.pageYOffset >= sectionTop - scrollOffset) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                const parent = link.parentElement;
                parent.classList.remove('selected');
                if (link.getAttribute('href') === '#' + current) {
                    parent.classList.add('selected');
                }
            });

            // Add/remove scrolled class for enhanced shadow
            if (navBarWrapper) {
                if (window.pageYOffset > navBarOffset) {
                    navBarWrapper.classList.add('scrolled');
                } else {
                    navBarWrapper.classList.remove('scrolled');
                }
            }
        }

        window.addEventListener('scroll', updateActiveNav);

        // Smooth scroll to sections with proper offset
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    // Calculate offset: header (118px) + nav-bar height (~52px) + padding (20px)
                    const headerHeight = 118;
                    const navBarHeight = navBarWrapper?.offsetHeight || 52;
                    const offset = headerHeight + navBarHeight + 20;
                    const offsetTop = targetSection.offsetTop - offset;

                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });

    // Star rating hover effect and submission
    document.addEventListener('DOMContentLoaded', function() {
        const starRating = document.querySelector('.star-rating');
        if (starRating) {
            const stars = starRating.querySelectorAll('.rating-star');
            let currentRating = {{ $userRating ? $userRating->rating : 0 }};

            // Hover effects
            stars.forEach((star, index) => {
                star.addEventListener('mouseenter', function() {
                    highlightStars(index + 1);
                });

                star.addEventListener('mouseleave', function() {
                    highlightStars(currentRating);
                });

                // Click handler for clearing rating
                star.addEventListener('click', function() {
                    const clickedRating = index + 1;
                    // If clicking the same rating, delete it
                    if (currentRating === clickedRating) {
                        deleteRating();
                    }
                });
            });

            function highlightStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.style.color = '#ffc107';
                    } else {
                        star.style.color = '#ddd';
                    }
                });
            }

            // Handle detailed rating form submission via AJAX
            const detailedRatingForm = document.getElementById('detailed-rating-form');
            if (detailedRatingForm) {
                const radioButtons = detailedRatingForm.querySelectorAll('input[type="radio"]');
                const ratingText = document.getElementById('rating-text');

                radioButtons.forEach((radio, index) => {
                    radio.addEventListener('change', function() {
                        const rating = parseInt(this.value);

                        // Submit via AJAX
                        fetch(detailedRatingForm.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ rating: rating })
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Update current rating for hover effects
                            currentRating = rating;
                            currentQuickRating = rating;

                            // Update star visual state in detailed form
                            highlightStars(rating);

                            // Update rating text
                            if (ratingText) {
                                ratingText.textContent = `Your rating: ${rating}/5`;
                            }

                            // Update quick rating stars at the top
                            const starButtons = document.querySelectorAll('.star-rating-row .star-btn');
                            starButtons.forEach((star, starIndex) => {
                                if (starIndex < rating) {
                                    star.classList.add('active');
                                    star.style.color = '#ffc107';
                                } else {
                                    star.classList.remove('active');
                                    star.style.color = '#ddd';
                                }
                            });

                            // Update Reviews & Ratings section
                            updateRatingStatistics(data);
                        })
                        .catch(error => {
                            console.error('Error submitting rating:', error);
                        });
                    });
                });
            }
        }
    });

    // Access Request Modal functions
    function openAccessRequestModal() {
        const modal = document.getElementById('accessRequestModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeAccessRequestModal() {
        const modal = document.getElementById('accessRequestModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Share Modal functions
    let qrcodeInstance = null;

    function openShareModal() {
        const modal = document.getElementById('shareModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Generate QR Code
            const qrcodeContainer = document.getElementById('qrcode');
            qrcodeContainer.innerHTML = ''; // Clear previous QR code

            if (typeof QRCode !== 'undefined') {
                qrcodeInstance = new QRCode(qrcodeContainer, {
                    text: window.location.href,
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } else {
                qrcodeContainer.innerHTML = '<p style="color: #dc3545;">QR Code library not loaded</p>';
            }
        }
    }

    function closeShareModal() {
        const modal = document.getElementById('shareModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';

            // Clear feedback message
            const feedback = document.getElementById('copyFeedback');
            if (feedback) {
                feedback.textContent = '';
            }
        }
    }

    function copyShareUrl() {
        const feedback = document.getElementById('copyFeedback');
        const url = window.location.href;

        navigator.clipboard.writeText(url).then(function() {
            feedback.textContent = '✓ Link copied to clipboard!';
            feedback.style.color = '#28a745';
            setTimeout(() => {
                feedback.textContent = '';
            }, 3000);
        }, function(err) {
            feedback.textContent = '✗ Failed to copy link';
            feedback.style.color = '#dc3545';
            console.error('Could not copy text: ', err);
        });
    }

    // Track current quick rating
    let currentQuickRating = {{ $userRating ? $userRating->rating : 0 }};

    // Quick star rating submission
    function submitQuickRating(rating) {
        const form = document.getElementById('quick-rating-form');

        if (form) {
            // If clicking the same star that's already selected, delete the rating
            if (currentQuickRating === rating) {
                deleteRating();
                return;
            }

            // Submit via AJAX to prevent page reload
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ rating: rating })
            })
            .then(response => response.json())
            .then(data => {
                // Update current rating tracker
                currentQuickRating = rating;

                // Show helper text for removal
                const helperText = document.getElementById('rating-helper-text');
                if (helperText) {
                    helperText.style.display = 'block';
                }

                // Update quick star visual state
                const starButtons = document.querySelectorAll('.star-rating-row .star-btn');
                starButtons.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('active');
                        star.style.color = '#ffc107';
                    } else {
                        star.classList.remove('active');
                        star.style.color = '#ddd';
                    }
                });

                // Update detailed rating form
                const detailedForm = document.getElementById('detailed-rating-form');
                if (detailedForm) {
                    // Update radio buttons
                    const radioButtons = detailedForm.querySelectorAll('input[type="radio"]');
                    radioButtons.forEach((radio, index) => {
                        radio.checked = (index + 1 === rating);
                    });

                    // Update detailed form stars
                    const detailedStars = detailedForm.querySelectorAll('.rating-star');
                    detailedStars.forEach((star, index) => {
                        if (index < rating) {
                            star.style.color = '#ffc107';
                        } else {
                            star.style.color = '#ddd';
                        }
                    });

                    // Update rating text
                    const ratingText = document.getElementById('rating-text');
                    if (ratingText) {
                        ratingText.textContent = `Your rating: ${rating}/5`;
                    }
                }

                // Update Reviews & Ratings section
                updateRatingStatistics(data);
            })
            .catch(error => {
                console.error('Error submitting rating:', error);
            });
        }
    }

    // Delete rating function
    function deleteRating() {
        const deleteUrl = '{{ route("library.rate.delete", $book->id) }}';

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Reset current rating trackers (both quick and detailed)
            currentQuickRating = 0;
            // Update the detailed form's currentRating if it exists
            if (typeof currentRating !== 'undefined') {
                currentRating = 0;
            }

            // Hide helper text
            const helperText = document.getElementById('rating-helper-text');
            if (helperText) {
                helperText.style.display = 'none';
            }

            // Clear all quick star visual state
            const starButtons = document.querySelectorAll('.star-rating-row .star-btn');
            starButtons.forEach((star) => {
                star.classList.remove('active');
                star.style.color = '#ddd';
            });

            // Update detailed rating form
            const detailedForm = document.getElementById('detailed-rating-form');
            if (detailedForm) {
                // Uncheck all radio buttons
                const radioButtons = detailedForm.querySelectorAll('input[type="radio"]');
                radioButtons.forEach((radio) => {
                    radio.checked = false;
                });

                // Clear detailed form stars
                const detailedStars = detailedForm.querySelectorAll('.rating-star');
                detailedStars.forEach((star) => {
                    star.style.color = '#ddd';
                });

                // Update rating text
                const ratingText = document.getElementById('rating-text');
                if (ratingText) {
                    ratingText.textContent = 'Click to rate';
                }
            }

            // Update Reviews & Ratings section
            updateRatingStatistics(data);
        })
        .catch(error => {
            console.error('Error deleting rating:', error);
        });
    }

    function updateRatingStatistics(data) {
        // Update average rating number
        const ratingScoreNumber = document.querySelector('.rating-score-number');
        if (ratingScoreNumber) {
            ratingScoreNumber.textContent = data.averageRating.toFixed(1);
        }

        // Update star display
        const ratingScoreStars = document.querySelector('.rating-score-stars');
        if (ratingScoreStars) {
            const stars = ratingScoreStars.querySelectorAll('.star');
            stars.forEach((star, index) => {
                if (index < Math.round(data.averageRating)) {
                    star.classList.remove('empty');
                } else {
                    star.classList.add('empty');
                }
            });
        }

        // Update total rating count
        const ratingScoreCount = document.querySelector('.rating-score-count');
        if (ratingScoreCount) {
            const plural = data.totalRatings === 1 ? 'rating' : 'ratings';
            ratingScoreCount.textContent = `${data.totalRatings} ${plural}`;
        }

        // Update rating distribution bars
        for (let i = 1; i <= 5; i++) {
            const count = data.ratingDistribution[i];
            const percentage = data.totalRatings > 0 ? (count / data.totalRatings) * 100 : 0;

            const barRow = document.querySelector(`.rating-bar-row:nth-child(${6 - i})`);
            if (barRow) {
                const barFill = barRow.querySelector('.rating-bar-fill');
                const barCount = barRow.querySelector('.rating-bar-count');

                if (barFill) barFill.style.width = `${percentage}%`;
                if (barCount) barCount.textContent = count;
            }
        }

        // Show rating section if it was hidden
        const ratingCenter = document.querySelector('.rating-center');
        const emptyState = document.querySelector('.rating-empty-state');
        if (ratingCenter && emptyState && data.totalRatings > 0) {
            ratingCenter.style.display = 'flex';
            emptyState.style.display = 'none';
        }

        // Update reader-stats-flex section (rating text only, no stars)
        const readerStatsTexts = document.querySelectorAll('.reader-stats-flex .stat-text');
        if (readerStatsTexts.length >= 3) {
            // The third stat-text is the rating
            const ratingStatText = readerStatsTexts[2];
            if (ratingStatText) {
                if (data.totalRatings > 0) {
                    const plural = data.totalRatings === 1 ? 'rating' : 'ratings';
                    ratingStatText.innerHTML = `<i class="fal fa-star"></i> <strong>${data.averageRating.toFixed(1)}</strong> (${data.totalRatings} ${plural})`;
                } else {
                    ratingStatText.innerHTML = '<i class="fal fa-star"></i> No ratings yet';
                }
            }
        }
    }

    // Star rating hover effects for quick rating
    document.addEventListener('DOMContentLoaded', function() {
        const starButtons = document.querySelectorAll('.star-rating-row .star-btn');

        starButtons.forEach((star, index) => {
            star.addEventListener('mouseenter', function() {
                starButtons.forEach((s, i) => {
                    if (i <= index) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });

        const starRatingRow = document.querySelector('.star-rating-row');
        if (starRatingRow) {
            starRatingRow.addEventListener('mouseleave', function() {
                starButtons.forEach((s) => {
                    if (s.classList.contains('active')) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        }
    });

    // Note editing functions
    function editNote(noteId) {
        // Hide the note content
        document.getElementById('note-content-' + noteId).style.display = 'none';
        // Show the edit form
        document.getElementById('note-edit-form-' + noteId).style.display = 'block';
    }

    function cancelEdit(noteId) {
        // Show the note content
        document.getElementById('note-content-' + noteId).style.display = 'block';
        // Hide the edit form
        document.getElementById('note-edit-form-' + noteId).style.display = 'none';
    }

    // Close modal when clicking outside
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('accessRequestModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeAccessRequestModal();
                }
            });
        }
    });

    // Book Cover 3D Tilt Effect
    document.addEventListener('DOMContentLoaded', function() {
        const bookCover = document.querySelector('.book-cover-section .book-cover');

        if (bookCover) {
            bookCover.addEventListener('mousemove', function(e) {
                const rect = bookCover.getBoundingClientRect();
                const x = e.clientX - rect.left; // X position within the element
                const y = e.clientY - rect.top;  // Y position within the element

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                // Calculate rotation based on mouse position
                const rotateX = ((y - centerY) / centerY) * -10; // Max 10 degrees
                const rotateY = ((x - centerX) / centerX) * 10;  // Max 10 degrees

                bookCover.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05)`;
            });

            bookCover.addEventListener('mouseleave', function() {
                bookCover.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
            });
        }
    });

    // Book Cover Lightbox Functions
    function openCoverLightbox() {
        const lightbox = document.getElementById('coverLightbox');
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closeCoverLightbox(event) {
        // Only close if clicking on the background or close button
        if (event && event.target.tagName === 'IMG') {
            return; // Don't close when clicking the image itself
        }

        const lightbox = document.getElementById('coverLightbox');
        lightbox.classList.remove('active');
        document.body.style.overflow = ''; // Restore scrolling
    }

    // Close lightbox with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const lightbox = document.getElementById('coverLightbox');
            if (lightbox.classList.contains('active')) {
                closeCoverLightbox(event);
            }
        }
    });

    // Toggle collapsible sections
    function toggleCollapsibleSection(headerElement) {
        const content = headerElement.nextElementSibling;
        const isCollapsed = content.classList.contains('collapsed');

        // Toggle collapsed state
        if (isCollapsed) {
            content.classList.remove('collapsed');
            headerElement.classList.add('expanded');
        } else {
            content.classList.add('collapsed');
            headerElement.classList.remove('expanded');
        }
    }
</script>
@endpush
