@extends('layouts.library')

@section('title', 'Profile Settings - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('description', 'Manage your account settings and preferences')

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

    .profile-header {
        padding: 2rem 0;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 2rem;
    }

    .profile-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 0.5rem 0;
    }

    .profile-header p {
        font-size: 1.1rem;
        color: #666;
        margin: 0;
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
        color: #1d496a;
    }

    .profile-nav-item.active {
        background: #e6f3f9;
        color: #1d496a;
        font-weight: 600;
        border-left: 3px solid #1d496a;
    }

    .profile-nav-item i {
        width: 20px;
        margin-right: 0.75rem;
        text-align: center;
    }

    .profile-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .profile-card-header {
        margin-bottom: 1.5rem;
    }

    .profile-card-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 0.5rem 0;
    }

    .profile-card-header p {
        font-size: 0.95rem;
        color: #666;
        margin: 0;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: #1d496a;
        box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
    }

    .form-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .form-help {
        color: #666;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: #1d496a;
        color: white;
        text-decoration: none;
        border: none;
        border-radius: 6px;
        font-weight: normal;
        cursor: pointer;
        transition: background 0.3s;
        font-size: 1rem;
    }

    .btn:hover {
        background: #005a87;
    }

    .btn-secondary {
        background: #6c757d;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    .btn-danger {
        background: #dc3545;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .alert {
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
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

@section('content')
<div class="container">
    <div class="profile-header">
        <h1>
            <i class="fas fa-user-circle"></i> My account
        </h1>
        <p>Manage your profile settings and activity</p>
    </div>

    <div class="profile-container">
        <!-- Sidebar Navigation -->
        <aside class="profile-sidebar">
            <nav class="profile-nav">
                <!-- Profile Settings Section -->
                <div class="profile-nav-section">
                    <div class="profile-nav-header">Profile settings</div>
                    <a href="{{ route('profile.edit') }}" class="profile-nav-item active">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </a>
                </div>

                <!-- My Activity Section -->
                <div class="profile-nav-section">
                    <div class="profile-nav-header">My activity</div>
                    <a href="{{ route('profile.activity') }}" class="profile-nav-item">
                        <i class="fas fa-chart-line"></i> Activity dashboard
                    </a>
                    <a href="{{ route('profile.ratings') }}" class="profile-nav-item">
                        <i class="fas fa-star"></i> My ratings
                    </a>
                    <a href="{{ route('profile.reviews') }}" class="profile-nav-item">
                        <i class="fas fa-comment"></i> My reviews
                    </a>
                    <a href="{{ route('profile.downloads') }}" class="profile-nav-item">
                        <i class="fas fa-download"></i> My downloads
                    </a>
                    <a href="{{ route('profile.bookmarks') }}" class="profile-nav-item">
                        <i class="fas fa-bookmark"></i> My bookmarks
                    </a>
                    <a href="{{ route('profile.notes') }}" class="profile-nav-item">
                        <i class="fas fa-sticky-note"></i> My notes
                    </a>
                    <a href="{{ route('profile.timeline') }}" class="profile-nav-item">
                        <i class="fas fa-clock"></i> Activity timeline
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="profile-main">
            @if (session('status') === 'profile-updated')
                <div class="alert alert-success">
                    <strong><i class="fas fa-check-circle"></i> Success!</strong> Your profile has been updated.
                </div>
            @endif

            @if (session('status') === 'password-updated')
                <div class="alert alert-success">
                    <strong><i class="fas fa-check-circle"></i> Success!</strong> Your password has been updated.
                </div>
            @endif

            <!-- Profile Information Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2>Profile information</h2>
                    <p>Update your account's profile information and email address.</p>
                </div>

                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
                        @error('email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Save changes
                    </button>
                </form>
            </div>

            <!-- Update Password Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2>Update password</h2>
                    <p>Ensure your account is using a long, random password to stay secure.</p>
                </div>

                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="form-group">
                        <label for="update_password_current_password" class="form-label">Current password</label>
                        <input type="password" id="update_password_current_password" name="current_password" class="form-input" autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="update_password_password" class="form-label">New password</label>
                        <input type="password" id="update_password_password" name="password" class="form-input" autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="update_password_password_confirmation" class="form-label">Confirm password</label>
                        <input type="password" id="update_password_password_confirmation" name="password_confirmation" class="form-input" autocomplete="new-password">
                        @error('password_confirmation', 'updatePassword')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn">
                        <i class="fas fa-key"></i> Update password
                    </button>
                </form>
            </div>

            <!-- Delete Account Card -->
            <div class="profile-card" style="border-color: #dc3545;">
                <div class="profile-card-header">
                    <h2 style="color: #dc3545;">Delete Account</h2>
                    <p>Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.</p>
                </div>

                <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                    @csrf
                    @method('delete')

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Confirm your password to delete account">
                        <div class="form-help">
                            <i class="fas fa-info-circle"></i> Enter your password to confirm account deletion.
                        </div>
                        @error('password', 'userDeletion')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
