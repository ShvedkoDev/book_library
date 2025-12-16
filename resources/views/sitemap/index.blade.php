@extends('layouts.library')

@section('title', 'Sitemap - FSM National VLA Curriculum')
@section('meta_description', 'Browse the complete site structure and find all pages and resources available on the FSM National Vernacular Language Arts Curriculum website.')

@section('content')
<div class="library-container">
    <div class="library-content-wrapper">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-sitemap" style="color: var(--coe-green-primary); margin-right: 0.5rem;"></i>
                    Sitemap
                </h1>
                <p class="page-description">
                    Browse the complete structure of our website and easily find all available pages and resources.
                </p>
            </div>

            <!-- Sitemap Content -->
            <div class="sitemap-wrapper">
            <!-- Main Pages Section -->
            <div class="sitemap-section">
                <h2 class="sitemap-section-title">
                    <i class="fas fa-home"></i>
                    Main Pages
                </h2>
                <ul class="sitemap-list">
                    <li class="sitemap-item">
                        <a href="{{ url('/') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li class="sitemap-item">
                        <a href="{{ route('library.index') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>Library (Book Collection)</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- CMS Pages Section -->
            @if($pages->isNotEmpty())
            <div class="sitemap-section">
                <h2 class="sitemap-section-title">
                    <i class="fas fa-file-alt"></i>
                    Content Pages
                </h2>
                <ul class="sitemap-list">
                    @foreach($pages as $page)
                    <li class="sitemap-item">
                        <a href="{{ route('pages.show', $page->slug) }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>{{ $page->title }}</span>
                        </a>
                        <span class="sitemap-meta">Updated: {{ $page->updated_at->format('M d, Y') }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- User Pages Section -->
            @auth
            <div class="sitemap-section">
                <h2 class="sitemap-section-title">
                    <i class="fas fa-user"></i>
                    My account
                </h2>
                <ul class="sitemap-list">
                    <li class="sitemap-item">
                        <a href="{{ route('profile.edit') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>Profile settings</span>
                        </a>
                    </li>
                    <li class="sitemap-item">
                        <a href="{{ route('bookmarks.index') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>My bookmarks</span>
                        </a>
                    </li>
                    <li class="sitemap-item">
                        <a href="{{ route('profile.activity') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>My activity</span>
                        </a>
                    </li>
                    <li class="sitemap-item">
                        <a href="{{ route('profile.ratings') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>My ratings</span>
                        </a>
                    </li>
                    <li class="sitemap-item">
                        <a href="{{ route('profile.reviews') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>My reviews</span>
                        </a>
                    </li>
                    <li class="sitemap-item">
                        <a href="{{ route('profile.downloads') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>My downloads</span>
                        </a>
                    </li>
                    <li class="sitemap-item">
                        <a href="{{ route('profile.notes') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>My notes</span>
                        </a>
                    </li>
                    <li class="sitemap-item">
                        <a href="{{ route('profile.timeline') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>Activity timeline</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endauth

            <!-- Authentication Pages Section -->
            @guest
            <div class="sitemap-section">
                <h2 class="sitemap-section-title">
                    <i class="fas fa-sign-in-alt"></i>
                    Account access
                </h2>
                <ul class="sitemap-list">
                    <li class="sitemap-item">
                        <a href="{{ route('login') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>Login</span>
                        </a>
                    </li>
                    <li class="sitemap-item">
                        <a href="{{ route('register') }}" class="sitemap-link">
                            <i class="fas fa-chevron-right"></i>
                            <span>Register</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endguest
        </div>
    </div>
</div>
</div>

<style>
    .sitemap-wrapper {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-top: 2rem;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 2.5rem;
        color: var(--coe-green-primary);
        margin-bottom: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .page-description {
        font-size: 1.1rem;
        color: #666;
        margin: 0;
    }

    .sitemap-section {
        margin-bottom: 3rem;
    }

    .sitemap-section:last-child {
        margin-bottom: 0;
    }

    .sitemap-section-title {
        font-size: 1.5rem;
        color: var(--coe-green-primary);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--coe-green-primary);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .sitemap-section-title i {
        font-size: 1.25rem;
    }

    .sitemap-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sitemap-item {
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        background: #f8f9fa;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .sitemap-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .sitemap-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        color: var(--coe-blue-primary);
        font-size: 1.05rem;
        font-weight: 500;
        flex: 1;
        transition: color 0.2s ease;
    }

    .sitemap-link:hover {
        color: var(--coe-green-primary);
    }

    .sitemap-link i {
        font-size: 0.875rem;
        color: var(--coe-green-primary);
    }

    .sitemap-text-only {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #666;
        font-size: 1.05rem;
    }

    .sitemap-text-only i {
        color: var(--coe-blue-primary);
    }

    .sitemap-meta {
        font-size: 0.875rem;
        color: #999;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .sitemap-wrapper {
            padding: 1.5rem;
        }

        .page-title {
            font-size: 2rem;
        }

        .sitemap-section-title {
            font-size: 1.25rem;
        }

        .sitemap-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .sitemap-meta {
            margin-left: 1.625rem;
        }
    }
</style>
@endsection
