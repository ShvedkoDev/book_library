@php
    $currentRoute = Route::currentRouteName();
@endphp

<aside class="profile-sidebar">
    <nav class="profile-nav">
        <!-- Profile Settings Section -->
        <div class="profile-nav-section">
            <div class="profile-nav-header">Profile settings</div>
            <a href="{{ route('profile.edit') }}" class="profile-nav-item {{ $currentRoute === 'profile.edit' ? 'active' : '' }}">
                <i class="fas fa-user-edit"></i> Edit Profile
            </a>
        </div>

        <!-- My Activity Section -->
        <div class="profile-nav-section">
            <div class="profile-nav-header">My activity</div>
            <a href="{{ route('profile.activity') }}" class="profile-nav-item {{ $currentRoute === 'profile.activity' ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Activity dashboard
            </a>
            <a href="{{ route('profile.ratings') }}" class="profile-nav-item {{ $currentRoute === 'profile.ratings' ? 'active' : '' }}">
                <i class="fas fa-star"></i> My ratings
            </a>
            <a href="{{ route('profile.reviews') }}" class="profile-nav-item {{ $currentRoute === 'profile.reviews' ? 'active' : '' }}">
                <i class="fas fa-comment"></i> My reviews
            </a>
            <a href="{{ route('profile.downloads') }}" class="profile-nav-item {{ $currentRoute === 'profile.downloads' ? 'active' : '' }}">
                <i class="fas fa-download"></i> My downloads
            </a>
            <a href="{{ route('profile.bookmarks') }}" class="profile-nav-item {{ $currentRoute === 'profile.bookmarks' ? 'active' : '' }}">
                <i class="fas fa-heart"></i> My bookmarks
            </a>
            <a href="{{ route('profile.notes') }}" class="profile-nav-item {{ $currentRoute === 'profile.notes' ? 'active' : '' }}">
                <i class="fas fa-sticky-note"></i> My notes
            </a>
            <a href="{{ route('profile.timeline') }}" class="profile-nav-item {{ $currentRoute === 'profile.timeline' ? 'active' : '' }}">
                <i class="fas fa-clock"></i> Activity timeline
            </a>
        </div>
    </nav>
</aside>

@push('styles')
<style>
    .profile-container {
        display: flex;
        gap: 2rem;
        margin-top: 2rem;
    }

    .profile-sidebar {
        width: 250px;
        flex-shrink: 0;
    }

    .profile-main {
        flex: 1;
    }

    .profile-nav {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
    }

    .profile-nav-section {
        border-bottom: 1px solid #e0e0e0;
    }

    .profile-nav-section:last-child {
        border-bottom: none;
    }

    .profile-nav-header {
        padding: 0.75rem 1rem;
        background: #f9f9f9;
        font-weight: 600;
        font-size: 0.875rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .profile-nav-item {
        display: flex;
        align-items: center;
        padding: 0.875rem 1rem;
        color: #555;
        text-decoration: none;
        transition: all 0.2s;
        border-bottom: 1px solid #f0f0f0;
    }

    .profile-nav-item:last-child {
        border-bottom: none;
    }

    .profile-nav-item:hover {
        background: #f9f9f9;
        color: #007cba;
    }

    .profile-nav-item.active {
        background: #e6f3f9;
        color: #007cba;
        font-weight: 600;
        border-left: 3px solid #007cba;
    }

    .profile-nav-item i {
        width: 20px;
        margin-right: 0.75rem;
        text-align: center;
    }

    @media (max-width: 968px) {
        .profile-container {
            flex-direction: column;
        }

        .profile-sidebar {
            width: 100%;
        }
    }
</style>
@endpush
