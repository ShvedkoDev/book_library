@extends('layouts.library')

@section('title', $book->title . ' - Micronesian Teachers Digital Library')
@section('description', Str::limit($book->description ?? 'Educational resource for Micronesian teachers', 160))
@section('og_type', 'book')
@section('og_image', $book->getThumbnailUrl())

@push('styles')
<style>
    /* CSS Variables for colors and common values */
    :root {
        /* Primary colors */
        --color-primary: #007cba;
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
        position: sticky;
        top: 2rem;
        height: fit-content;
    }

    .book-cover-section .book-cover {
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin-bottom: 1rem;
    }

    .access-status {
        padding: 0.5rem;
        border-radius: 4px;
        text-align: center;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .access-status.full-access {
        background-color: #d4edda;
        color: #155724;
    }

    .access-status.limited-access {
        background-color: #fff3cd;
        color: #856404;
    }

    .access-status.unavailable {
        background-color: #f8d7da;
        color: #721c24;
    }

    .book-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .book-action-btn {
        padding: 0.5rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
        text-align: center;
        width: 100%;
        text-decoration: none;
        display: block;
    }

    .book-action-btn.btn-primary {
        background-color: #007cba;
        color: white;
    }

    .book-action-btn.btn-secondary {
        background-color: #f0f0f0;
        color: #333;
    }

    .book-rating {
        border-top: 1px solid #e0e0e0;
        padding-top: 1rem;
    }

    .stars {
        color: #ffc107;
        font-size: 1.2rem;
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
        color: #007cba;
        text-decoration: none;
        font-size: 0.875rem;
    }

    .book-title {
        font-size: 2rem;
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
        font-size: 1rem;
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
        color: #007cba;
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
        color: #007cba;
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
        display: block;
        padding: 0.5rem 1.25rem;
        color: #666;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        border-radius: 20px;
        transition: all 0.2s ease;
        white-space: nowrap;
        background: transparent;
    }

    .nav-bar li.selected a {
        background: #007cba;
        background-color: #007cba;
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
        top: -200px;
        visibility: hidden;
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
        color: #007cba;
        border-bottom-color: #007cba;
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

    .detail-group h3 {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        color: #333;
    }

    .detail-item {
        display: flex;
        margin-bottom: 0.75rem;
    }

    .detail-label {
        font-weight: 600;
        min-width: 140px;
        color: #555;
    }

    .detail-value {
        color: #333;
    }

    .related-books {
        margin-top: 3rem;
    }

    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(156px, 1fr));
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .book-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.5rem;
        text-align: center;
        transition: box-shadow 0.3s;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .book-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .book-card-cover {
        width: 100%;
        border-radius: 4px;
        margin-bottom: 0.75rem;
    }

    .book-card-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.75rem;
    }

    .book-card-author {
        font-size: 0.65rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .book-card-meta {
        font-size: 0.65rem;
        color: #999;
        margin-bottom: 0.75rem;
    }

    .book-card-btn {
        width: 100%;
        padding: 0.5rem;
        background-color: #007cba;
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
        color: #007cba;
        text-decoration: none;
        margin-right: 0.5rem;
        transition: color 0.2s;
    }

    .link-box a:hover {
        color: #005a8a;
        text-decoration: underline;
    }

    .link-box a:after {
        content: ",";
        color: #666;
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
        color: #007cba;
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
        color: #007cba;
        cursor: pointer;
        font-weight: 600;
        padding: 0.5rem 0;
        font-size: 0.95rem;
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

    /* Book Info Cards (OpenLibrary style) */
    .book-info-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.75rem;
        margin-bottom: var(--spacing-lg);
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
        margin-top: var(--spacing-3xl);
        padding: var(--spacing-2xl);
        background: var(--color-bg-gray);
        border-radius: var(--radius-xl);
    }

    .reviews-section h2 {
        margin-bottom: var(--spacing-xl);
        color: var(--color-text-primary);
    }

    .rating-histogram,
    .user-rating-form,
    .user-review-form,
    .review-item,
    .review-guest-message {
        margin-bottom: var(--spacing-2xl);
        padding: var(--spacing-xl);
        background: var(--color-bg-white);
        border-radius: var(--radius-xl);
    }

    .rating-histogram h3,
    .user-rating-form h3,
    .user-review-form h3 {
        font-size: var(--font-2xl);
        margin-bottom: var(--spacing-lg);
        color: var(--color-text-light);
    }

    .rating-center {
        display: flex;
        gap: var(--spacing-2xl);
        align-items: center;
    }

    .rating-score-display {
        text-align: center;
    }

    .rating-score-number {
        font-size: var(--font-5xl);
        font-weight: bold;
        color: var(--color-primary);
    }

    .rating-score-stars {
        font-size: var(--font-3xl);
    }

    .rating-score-count {
        color: var(--color-text-secondary);
        margin-top: var(--spacing-sm);
    }

    .rating-bars {
        flex: 1;
    }

    .rating-bar-row {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-sm);
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
        padding: var(--spacing-2xl);
    }

    /* Rating Form */
    .star-rating-form {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
    }

    .star-rating {
        display: flex;
        gap: var(--spacing-sm);
    }

    .star-rating label {
        cursor: pointer;
        font-size: var(--font-4xl);
    }

    .rating-text {
        color: var(--color-text-secondary);
    }

    /* Review Form */
    .review-form-field {
        width: 100%;
        padding: var(--spacing-lg);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        resize: vertical;
        font-family: inherit;
    }

    .review-form-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: var(--spacing-lg);
    }

    .review-form-note {
        color: var(--color-text-muted);
        font-size: var(--font-base);
    }

    .btn-submit {
        padding: var(--spacing-md) var(--spacing-xl);
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: var(--radius-lg);
        cursor: pointer;
        font-weight: 600;
    }

    /* Review Item */
    .review-item {
        padding: var(--spacing-xl);
        background: var(--color-bg-white);
        border-radius: var(--radius-xl);
        margin-bottom: var(--spacing-lg);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: var(--spacing-sm);
    }

    .review-author {
        font-weight: bold;
        color: var(--color-text-primary);
    }

    .review-rating {
        display: inline-block;
        margin-left: var(--spacing-lg);
        font-size: var(--font-lg);
    }

    .review-date {
        color: var(--color-text-muted);
        font-size: var(--font-base);
    }

    .review-text {
        color: var(--color-text-light);
        line-height: 1.6;
        margin: 0;
    }

    .guest-message {
        text-align: center;
    }

    .guest-message p {
        color: var(--color-text-secondary);
        margin-bottom: var(--spacing-lg);
    }

    .guest-message a {
        color: var(--color-primary);
        text-decoration: underline;
    }

    /* Notes Section */
    .notes-section {
        margin-top: var(--spacing-3xl);
        padding: var(--spacing-2xl);
        background: var(--color-bg-light);
        border-radius: var(--radius-xl);
    }

    .notes-section h2 {
        margin-bottom: var(--spacing-xl);
        color: var(--color-text-primary);
    }

    .notes-section h2 span {
        font-size: var(--font-base);
        color: var(--color-text-secondary);
        font-weight: 400;
    }

    .add-note-form,
    .note-item {
        margin-bottom: var(--spacing-2xl);
        padding: var(--spacing-xl);
        background: var(--color-bg-white);
        border-radius: var(--radius-xl);
    }

    .add-note-form h3,
    .existing-notes h3 {
        font-size: var(--font-xl);
        margin-bottom: var(--spacing-lg);
        color: var(--color-text-light);
    }

    .note-field-label {
        display: block;
        margin-bottom: var(--spacing-sm);
        font-weight: 600;
        color: var(--color-text-primary);
    }

    .note-field-input {
        width: 100%;
        padding: var(--spacing-md);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        resize: vertical;
        font-family: inherit;
    }

    .note-field-small {
        font-size: var(--font-base);
        color: var(--color-text-secondary);
    }

    .note-field-page-input {
        width: 150px;
        padding: var(--spacing-md);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        font-family: inherit;
    }

    .note-field-margin {
        margin-bottom: var(--spacing-lg);
    }

    .btn-add-note {
        padding: var(--spacing-md) var(--spacing-xl);
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: var(--radius-lg);
        cursor: pointer;
        font-weight: 600;
        transition: background var(--transition-normal);
    }

    .note-item {
        border-left: 4px solid var(--color-primary);
    }

    .note-item-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: var(--spacing-md);
    }

    .note-item-header > div:first-child {
        flex: 1;
    }

    .note-page-badge {
        display: inline-block;
        background: var(--color-bg-light-gray);
        color: var(--color-text-secondary);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius-md);
        font-size: var(--font-sm);
        margin-bottom: var(--spacing-sm);
    }

    .note-date {
        color: var(--color-text-muted);
        font-size: var(--font-base);
    }

    .note-actions {
        display: flex;
        gap: var(--spacing-sm);
    }

    .btn-note-edit,
    .btn-note-delete {
        background: none;
        padding: var(--spacing-sm) var(--spacing-lg);
        border-radius: var(--radius-md);
        cursor: pointer;
        font-size: var(--font-base);
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
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .note-edit-form {
        display: none;
        margin-top: var(--spacing-lg);
    }

    .note-edit-form textarea {
        width: 100%;
        padding: var(--spacing-md);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        resize: vertical;
        font-family: inherit;
        margin-bottom: var(--spacing-sm);
    }

    .note-edit-actions {
        display: flex;
        gap: var(--spacing-sm);
    }

    .btn-note-save {
        padding: var(--spacing-sm) var(--spacing-lg);
        background: var(--color-approved);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        font-size: var(--font-base);
    }

    .btn-note-cancel {
        padding: var(--spacing-sm) var(--spacing-lg);
        background: #6c757d;
        color: white;
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        font-size: var(--font-base);
    }

    .notes-empty-state {
        padding: var(--spacing-2xl);
        background: var(--color-bg-white);
        border-radius: var(--radius-xl);
        text-align: center;
    }

    .notes-empty-state p {
        color: var(--color-text-muted);
    }

    .notes-guest-section {
        margin-top: var(--spacing-3xl);
        padding: var(--spacing-2xl);
        background: var(--color-bg-light);
        border-radius: var(--radius-xl);
        text-align: center;
    }

    .notes-guest-section h2 {
        margin-bottom: var(--spacing-lg);
        color: var(--color-text-primary);
    }

    .notes-guest-section p {
        color: var(--color-text-secondary);
        margin-bottom: var(--spacing-xl);
    }

    /* Access Request Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: var(--color-bg-white);
        border-radius: var(--radius-xl);
        padding: var(--spacing-2xl);
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-xl);
    }

    .modal-header h2 {
        margin: 0;
        color: var(--color-text-primary);
    }

    .modal-close {
        background: none;
        border: none;
        font-size: var(--font-4xl);
        cursor: pointer;
        color: var(--color-text-muted);
    }

    .modal-description {
        color: var(--color-text-secondary);
        margin-bottom: var(--spacing-xl);
    }

    .modal-actions {
        display: flex;
        gap: var(--spacing-lg);
        justify-content: flex-end;
    }

    .btn-modal-cancel {
        padding: var(--spacing-md) var(--spacing-xl);
        background: var(--color-bg-light-gray);
        color: var(--color-text-primary);
        border: none;
        border-radius: var(--radius-lg);
        cursor: pointer;
        font-weight: 600;
    }

    .btn-modal-submit {
        padding: var(--spacing-md) var(--spacing-xl);
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: var(--radius-lg);
        cursor: pointer;
        font-weight: 600;
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

    .main a:not(.button):not(.btn-primary) {
        color: var(--color-white);
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
                $pdfFile = $book->files->where('file_type', 'pdf')->where('is_primary', true)->first();
            @endphp

            <img src="{{ $book->getThumbnailUrl() }}" alt="{{ $book->title }}" class="book-cover">

            <div class="access-status {{ $book->access_level === 'full' ? 'full-access' : ($book->access_level === 'limited' ? 'limited-access' : 'unavailable') }}">
                <span>
                    @if($book->access_level === 'full')
                        📖 Full Access
                    @elseif($book->access_level === 'limited')
                        📄 Limited Access
                    @else
                        🔒 Unavailable
                    @endif
                </span>
            </div>

            <div class="book-actions">
                @if($book->access_level === 'full' && $pdfFile)
                    @auth
                        <a href="{{ route('library.view-pdf', ['book' => $book->id, 'file' => $pdfFile->id]) }}" target="_blank" class="book-action-btn btn-primary">View PDF</a>
                        <a href="{{ route('library.download', ['book' => $book->id, 'file' => $pdfFile->id]) }}" class="book-action-btn btn-secondary">Download PDF</a>
                    @else
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="book-action-btn btn-primary" title="Please log in to view PDF">Login to View PDF</a>
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="book-action-btn btn-secondary" title="Please log in to download">Login to Download</a>
                    @endauth
                @elseif($book->access_level === 'limited' && $pdfFile)
                    @auth
                        <a href="{{ route('library.view-pdf', ['book' => $book->id, 'file' => $pdfFile->id]) }}" target="_blank" class="book-action-btn btn-primary">Limited Preview</a>
                        <button class="book-action-btn btn-secondary" disabled>Request Full Access</button>
                    @else
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="book-action-btn btn-primary" title="Please log in to preview">Login to Preview</a>
                    @endauth
                @else
                    <button class="book-action-btn btn-primary" disabled>Not Available</button>
                    @auth
                        @if($userAccessRequest)
                            @if($userAccessRequest->status === 'pending')
                                <div class="status-box status-pending">
                                    <strong>⏳ Request Pending</strong>
                                    <p>Your access request is being reviewed.</p>
                                </div>
                            @elseif($userAccessRequest->status === 'approved')
                                <div class="status-box status-approved">
                                    <strong>✓ Access Approved</strong>
                                    <p>Your request has been approved. Check your email for instructions.</p>
                                </div>
                            @elseif($userAccessRequest->status === 'rejected')
                                <div class="status-box status-rejected">
                                    <strong>✗ Request Rejected</strong>
                                    <p>Your previous request was not approved.</p>
                                </div>
                                <button onclick="openAccessRequestModal()" class="book-action-btn btn-secondary">
                                    Request Again
                                </button>
                            @else
                                <button onclick="openAccessRequestModal()" class="book-action-btn btn-secondary">
                                    Request Access
                                </button>
                            @endif
                        @else
                            <button onclick="openAccessRequestModal()" class="book-action-btn btn-secondary">
                                Request Access
                            </button>
                        @endif
                    @else
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                           class="book-action-btn btn-secondary text-link"
                           title="Please log in to request access">
                            Login to Request Access
                        </a>
                    @endauth
                @endif
            </div>

            <!-- Bookmark Button (Auth Required) -->
            <div class="divider-top">
                @auth
                    <x-bookmark-button
                        :book="$book"
                        :isBookmarked="$book->isBookmarkedBy(Auth::id())"
                    />
                @else
                    <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="book-action-btn btn-secondary" title="Please log in to save to collection">
                        Login to Save to Collection
                    </a>
                @endauth
            </div>

            <!-- Share Button (No Auth Required) -->
            <div class="divider-top">
                <x-share-button
                    :url="route('library.show', $book->slug)"
                    :title="$book->title"
                    :description="Str::limit($book->description ?? 'Educational resource for Micronesian teachers', 100)"
                />
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
                        <a href="#reader-observations">Reviews ({{ $book->reviews->count() }})</a>
                    </li>
                    <li>
                        <a href="#library">Library Locations</a>
                    </li>
                    <li>
                        <a href="#related-books">Related Books</a>
                    </li>
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
                <h1 class="book-title">{{ $book->title }}</h1>
                @if($book->subtitle)
                    <h2 class="book-subtitle">{{ $book->subtitle }}</h2>
                @endif
                <div class="book-author">
                    @if($book->creators->isNotEmpty())
                        by {{ $book->creators->pluck('name')->join(', ') }}
                    @endif
                </div>

                <!-- Reader Stats -->
                <div class="reader-stats-flex">
                    <div class="reader-stat-item">
                        <div class="stars">
                            @php $roundedRating = round($averageRating); @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star {{ $i <= $roundedRating ? '' : 'empty' }}">★</span>
                            @endfor
                        </div>
                        <span class="stat-text">
                            @if($totalRatings > 0)
                                {{ number_format($averageRating, 1) }} ({{ $totalRatings }} {{ Str::plural('rating', $totalRatings) }})
                            @else
                                No ratings yet
                            @endif
                        </span>
                    </div>
                    <div class="stat-text">
                        <i class="fal fa-eye"></i> <strong>{{ number_format($book->view_count) }}</strong> {{ Str::plural('view', $book->view_count) }}
                    </div>
                    <div class="stat-text">
                        <i class="fal fa-download"></i> <strong>{{ number_format($book->download_count) }}</strong> {{ Str::plural('download', $book->download_count) }}
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
                    <span class="info-card-label">Publish Date</span>
                    <span class="info-card-value">{{ $book->publication_year ?? 'N/A' }}</span>
                </div>
                <div class="info-card">
                    <span class="info-card-label">Publisher</span>
                    <span class="info-card-value publisher">
                        @if($book->publisher)
                            {{ $book->publisher->name }}
                        @else
                            N/A
                        @endif
                    </span>
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
            @if($book->purposeClassifications->isNotEmpty() || $book->learnerLevelClassifications->isNotEmpty() || $book->keywords->isNotEmpty())
                <div class="">
                    <div class="subjects-content">
                        <!-- People Section -->
                        @if($book->authors->isNotEmpty() || $book->illustrators->isNotEmpty())
                            @if($book->authors->isNotEmpty())
                                <div class="link-box">
                                    <h3 class="details-subsection-title">Authors</h3>
                                    @foreach($book->authors as $author)
                                        <p class="details-value">{{ $author->name }}</p>
                                    @endforeach
                                </div>
                            @endif

                            @if($book->illustrators->isNotEmpty())
                                <div class="link-box">
                                    <h3 class="details-subsection-title">Illustrators</h3>
                                    @foreach($book->illustrators as $illustrator)
                                        <p class="details-value">{{ $illustrator->name }}</p>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        @if($book->purposeClassifications->isNotEmpty())
                            <div class="link-box">
                                <h3 class="details-subsection-title">Subjects</h3>
                                @foreach($book->purposeClassifications as $classification)
                                    <p class="details-value">{{ $classification->value }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if($book->learnerLevelClassifications->isNotEmpty())
                            <div class="link-box">
                                <h3 class="details-subsection-title">Grade Levels</h3>
                                @foreach($book->learnerLevelClassifications as $classification)
                                    <p class="details-value">{{ $classification->value }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if($book->keywords && $book->keywords->isNotEmpty())
                            <div class="link-box">
                                <h3 class="details-subsection-title">Keywords</h3>
                                @foreach($book->keywords as $keywordObj)
                                    <span class="details-value">
                                        {{ $keywordObj->keyword }}
                                    </span>
                                @endforeach
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
                            <h3>Table of Contents</h3>
                            <div class="section-text-wide">{!! nl2br(e($book->table_of_contents)) !!}</div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Details Section -->
            <a id="details" name="details" class="section-anchor"></a>
            <div class="tab-section">
                <h2 class="section-title text-left">Book Details</h2>
                <hr class="section-separator">

                <div class="details-section">
                    <!-- Edition Notes -->
                    <div class="details-subsection">
                        <h3 class="details-subsection-title">Edition Notes</h3>
                        @if($book->publisher)
                            <div class="details-row">
                                <span class="details-label">Publisher</span>
                                <span class="details-value">{{ $book->publisher->name }}</span>
                            </div>
                        @endif
                        @if($book->publication_year)
                            <div class="details-row">
                                <span class="details-label">Copyright Date</span>
                                <span class="details-value">{{ $book->publication_year }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Classifications -->
                    @if($book->purposeClassifications->isNotEmpty() || $book->learnerLevelClassifications->isNotEmpty())
                        <div class="details-subsection">
                            <h3 class="details-subsection-title">Classifications</h3>
                            @if($book->purposeClassifications->isNotEmpty())
                                <div class="details-row">
                                    <span class="details-label">Subject</span>
                                    <span class="details-value">{{ $book->purposeClassifications->pluck('value')->join(', ') }}</span>
                                </div>
                            @endif
                            @if($book->learnerLevelClassifications->isNotEmpty())
                                <div class="details-row">
                                    <span class="details-label">Grade Level</span>
                                    <span class="details-value">{{ $book->learnerLevelClassifications->pluck('value')->join(', ') }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- The Physical Object -->
                    @if($book->pages || $book->physical_type)
                        <div class="details-subsection">
                            <h3 class="details-subsection-title">The Physical Object</h3>
                            @if($book->pages)
                                <div class="details-row">
                                    <span class="details-label">Number of pages</span>
                                    <span class="details-value">{{ $book->pages }}</span>
                                </div>
                            @endif
                            @if($book->physical_type)
                                <div class="details-row">
                                    <span class="details-label">Format</span>
                                    <span class="details-value">{{ $book->physical_type }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Edition Identifiers -->
                    @if($book->isbn_10 || $book->isbn_13 || $book->palm_code || $book->internal_id)
                        <div class="details-subsection">
                            <h3 class="details-subsection-title">Edition Identifiers</h3>
                            @if($book->internal_id)
                                <div class="details-row">
                                    <span class="details-label">MTDL ID</span>
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
                                    <span class="details-label">PALM Code</span>
                                    <span class="details-value">{{ $book->palm_code }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Work Identifiers -->
                    @if($book->id)
                        <div class="details-subsection">
                            <h3 class="details-subsection-title">Work Identifiers</h3>
                            <div class="details-row">
                                <span class="details-label">Work ID</span>
                                <span class="details-value">{{ $book->id }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Library Locations Section -->
            <a id="library" name="library" class="section-anchor"></a>
            <div class="tab-section">
                <h2 class="section-title text-left">Library Locations</h2>
                <hr class="section-separator">
                @if($book->libraryReferences->isNotEmpty())
                    @foreach($book->libraryReferences as $reference)
                        <div class="detail-group section-wrapper-small">
                            <h3>{{ $reference->library_name }}</h3>
                            @if($reference->reference_number)
                                <div class="detail-item">
                                    <span class="detail-label">Reference Number:</span>
                                    <span class="detail-value">{{ $reference->reference_number }}</span>
                                </div>
                            @endif
                            @if($reference->call_number)
                                <div class="detail-item">
                                    <span class="detail-label">Call Number:</span>
                                    <span class="detail-value">{{ $reference->call_number }}</span>
                                </div>
                            @endif
                            @if($reference->catalog_link)
                                <div class="detail-item">
                                    <span class="detail-label">Catalog:</span>
                                    <span class="detail-value">
                                        <a href="{{ $reference->catalog_link }}" target="_blank">View in Library Catalog</a>
                                    </span>
                                </div>
                            @endif
                            @if($reference->notes)
                                <div class="detail-item">
                                    <span class="detail-label">Notes:</span>
                                    <span class="detail-value">{{ $reference->notes }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p>No physical library references available for this book.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Related Books Sections -->
    <a id="related-books" name="related-books" class="section-anchor"></a>
    <x-library.related-books :books="$relatedByCollection" title="More books from the same collection" sectionId="related-by-collection" />
    <x-library.related-books :books="$relatedByLanguage" title="More books in the same language" sectionId="related-by-language" />
    <x-library.related-books :books="$relatedByCreator" title="More books by the same author" sectionId="related-by-creator" />

    <!-- Reviews and Ratings Section -->
    <a id="reader-observations" name="reader-observations" class="section-anchor"></a>
    <div class="reviews-section">
        <h2>Reviews & Ratings</h2>

        <!-- Rating Histogram -->
        <div class="rating-histogram">
            <h3 class="section-title text-left">Rating Distribution</h3>
            @if($totalRatings > 0)
                <div class="rating-center">
                    <div class="rating-score-display">
                        <div class="rating-score-number">{{ number_format($averageRating, 1) }}</div>
                        <div class="stars rating-score-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star {{ $i <= round($averageRating) ? '' : 'empty' }}">★</span>
                            @endfor
                        </div>
                        <div class="rating-score-count">{{ $totalRatings }} {{ Str::plural('rating', $totalRatings) }}</div>
                    </div>
                    <div class="rating-bars">
                        @foreach([5, 4, 3, 2, 1] as $rating)
                            @php
                                $count = $ratingDistribution[$rating];
                                $percentage = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0;
                            @endphp
                            <div class="rating-bar-row">
                                <span class="rating-bar-label">{{ $rating }} stars</span>
                                <div class="rating-bar-container">
                                    <div class="rating-bar-fill" style="width: {{ $percentage }}%;"></div>
                                </div>
                                <span class="rating-bar-count">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="rating-empty-state">No ratings yet. Be the first to rate this book!</p>
            @endif
        </div>

        <!-- User Rating Form -->
        @auth
            <div class="user-rating-form">
                <h3 class="section-title text-left">Rate this book</h3>
                <form action="{{ route('library.rate', $book->id) }}" method="POST" class="star-rating-form">
                    @csrf
                    <div class="star-rating">
                        @for($i = 1; $i <= 5; $i++)
                            <label>
                                <input type="radio" name="rating" value="{{ $i }}" style="display: none;"
                                    {{ $userRating && $userRating->rating == $i ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                <span class="rating-star" data-rating="{{ $i }}"
                                    style="color: {{ $userRating && $i <= $userRating->rating ? '#ffc107' : '#ddd' }}; transition: color 0.2s;">★</span>
                            </label>
                        @endfor
                    </div>
                    @if($userRating)
                        <span class="rating-text">Your rating: {{ $userRating->rating }}/5</span>
                    @else
                        <span class="rating-text">Click to rate</span>
                    @endif
                </form>
            </div>
        @else
            <div class="review-guest-message guest-message">
                <p>Please <a href="{{ route('login', ['redirect' => url()->current()]) }}">log in</a> to rate this book.</p>
            </div>
        @endauth

        <!-- User Review Form -->
        @auth
            <div class="user-review-form">
                <h3 class="section-title text-left">Write a review</h3>
                <form action="{{ route('library.review', $book->id) }}" method="POST">
                    @csrf
                    <textarea name="review" rows="5" placeholder="Share your thoughts about this book..."
                        class="review-form-field"
                        required minlength="10" maxlength="2000"></textarea>
                    <div class="review-form-footer">
                        <span class="review-form-note">Reviews are moderated and will appear after approval.</span>
                        <button type="submit" class="btn-submit">
                            Submit Review
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="review-guest-message guest-message">
                <p>Please <a href="{{ route('login', ['redirect' => url()->current()]) }}">log in</a> to write a review.</p>
            </div>
        @endauth

        <!-- Existing Reviews -->
        <div class="existing-reviews">
            <h3 class="section-title text-left">User Reviews ({{ $book->reviews->count() }})</h3>
            @forelse($book->reviews as $review)
                <div class="review-item">
                    <div class="review-header">
                        <div>
                            <span class="review-author">{{ $review->user->name }}</span>
                            @php
                                $reviewUserRating = $book->ratings()->where('user_id', $review->user_id)->first();
                            @endphp
                            @if($reviewUserRating)
                                <div class="stars review-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="star {{ $i <= $reviewUserRating->rating ? '' : 'empty' }}">★</span>
                                    @endfor
                                </div>
                            @endif
                        </div>
                        <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="review-text">{{ $review->review }}</p>
                </div>
            @empty
                <div class="review-item guest-message">
                    <p>No reviews yet. Be the first to review this book!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Personal Notes Section -->
    @auth
    <div class="notes-section">
        <h2>
            <i class="fal fa-sticky-note"></i> My Notes
            @if($userNotes->isNotEmpty())
                <span>({{ $userNotes->count() }})</span>
            @endif
        </h2>

        <!-- Add New Note Form -->
        <div class="add-note-form">
            <h3 class="section-title text-left">Add a New Note</h3>
            <form action="{{ route('library.notes.store', $book->id) }}" method="POST">
                @csrf
                <div class="note-field-margin">
                    <label for="note" class="note-field-label">Note *</label>
                    <textarea
                        name="note"
                        id="note"
                        rows="4"
                        placeholder="Write your thoughts, observations, or reminders about this book..."
                        class="note-field-input"
                        required
                        minlength="1"
                        maxlength="5000"></textarea>
                    <small class="note-field-small">Maximum 5,000 characters. Your notes are private.</small>
                </div>

                <div class="note-field-margin">
                    <label for="page_number" class="note-field-label">Page Number (optional)</label>
                    <input
                        type="number"
                        name="page_number"
                        id="page_number"
                        min="1"
                        placeholder="e.g., 42"
                        class="note-field-page-input">
                </div>

                <button type="submit" class="btn-add-note">
                    <i class="fal fa-plus"></i> Add Note
                </button>
            </form>
        </div>

        <!-- Existing Notes -->
        <div class="existing-notes">
            <h3 class="section-title text-left">
                Your Notes
                @if($userNotes->isEmpty())
                    <span>(None yet)</span>
                @endif
            </h3>
            @forelse($userNotes as $note)
                <div class="note-item">
                    <div class="note-item-header">
                        <div>
                            @if($note->page_number)
                                <span class="note-page-badge">
                                    <i class="fal fa-book-open"></i> Page {{ $note->page_number }}
                                </span>
                            @endif
                            <div class="note-date">
                                <i class="fal fa-clock"></i> {{ $note->created_at->format('M d, Y') }}
                                @if($note->created_at != $note->updated_at)
                                    (edited {{ $note->updated_at->diffForHumans() }})
                                @endif
                            </div>
                        </div>
                        <div class="note-actions">
                            <button onclick="editNote({{ $note->id }})" class="btn-note-edit">
                                <i class="fal fa-edit"></i> Edit
                            </button>
                            <form action="{{ route('library.notes.destroy', $note->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this note?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-note-delete">
                                    <i class="fal fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <div id="note-content-{{ $note->id }}" class="note-content">{{ $note->note }}</div>

                    <!-- Edit Form (Hidden by default) -->
                    <div id="note-edit-form-{{ $note->id }}" class="note-edit-form">
                        <form action="{{ route('library.notes.update', $note->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <textarea
                                name="note"
                                rows="4"
                                required
                                minlength="1"
                                maxlength="5000">{{ $note->note }}</textarea>
                            <div class="note-field-margin">
                                <input
                                    type="number"
                                    name="page_number"
                                    value="{{ $note->page_number }}"
                                    min="1"
                                    placeholder="Page number (optional)"
                                    class="note-field-page-input">
                            </div>
                            <div class="note-edit-actions">
                                <button type="submit" class="btn-note-save">
                                    <i class="fal fa-check"></i> Save
                                </button>
                                <button type="button" onclick="cancelEdit({{ $note->id }})" class="btn-note-cancel">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="notes-empty-state">
                    <p>You haven't added any notes for this book yet. Use the form above to add your first note!</p>
                </div>
            @endforelse
        </div>
    </div>
    @else
        <div class="notes-guest-section">
            <h2>
                <i class="fal fa-sticky-note"></i> Personal Notes
            </h2>
            <p>Please <a href="{{ route('login') }}">log in</a> to add personal notes to this book.</p>
        </div>
    @endauth

    <!-- Access Request Modal -->
    @auth
    <div id="accessRequestModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Request Access</h2>
                <button onclick="closeAccessRequestModal()" class="modal-close">&times;</button>
            </div>

            <p class="modal-description">
                Fill out the form below to request access to <strong>{{ $book->title }}</strong>. We will review your request and contact you via email.
            </p>

            <form action="{{ route('library.request-access', $book->id) }}" method="POST">
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
                    <label for="access_request_message" class="note-field-label">Message (Optional)</label>
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
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endauth
</div>
@endsection

@push('scripts')
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

    // Sticky Navigation Active State
    document.addEventListener('DOMContentLoaded', function() {
        const sections = document.querySelectorAll('.section-anchor');
        const navLinks = document.querySelectorAll('.nav-bar a');
        const navBarWrapper = document.querySelector('.nav-bar-wrapper');
        let navBarOffset = navBarWrapper ? navBarWrapper.offsetTop : 0;

        // Update active state based on scroll position
        function updateActiveNav() {
            let current = '';

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (window.pageYOffset >= sectionTop - 200) {
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

        // Smooth scroll to sections
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    const offsetTop = targetSection.offsetTop - 200;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });

    // Star rating hover effect
    document.addEventListener('DOMContentLoaded', function() {
        const starRating = document.querySelector('.star-rating');
        if (starRating) {
            const stars = starRating.querySelectorAll('.rating-star');
            let currentRating = {{ $userRating ? $userRating->rating : 0 }};

            stars.forEach((star, index) => {
                star.addEventListener('mouseenter', function() {
                    highlightStars(index + 1);
                });

                star.addEventListener('mouseleave', function() {
                    highlightStars(currentRating);
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
</script>
@endpush
