{{-- Login Modal Component --}}
@php
    // Get demo users for development/testing
    $demoUsers = \App\Models\User::whereIn('email', [
        'admin@micronesianlib.edu',
        'maria.teacher@example.com',
        'john.educator@example.com',
        'sarah.prof@uog.edu',
        'james.lib@fsmgov.org'
    ])->get(['name', 'email', 'role']);

    // Check if we should auto-open the modal (when submitted from modal with errors)
    $shouldAutoOpen = old('from_modal') && ($errors->any() || session('status'));
@endphp

<div id="loginModal" class="auth-modal" style="display: {{ $shouldAutoOpen ? 'flex' : 'none' }};">

    <div class="auth-modal-backdrop" onclick="closeLoginModal()"></div>
    <div class="auth-modal-content">
        <div class="auth-modal-header">
            <h2>Log in</h2>
            <button type="button" class="auth-modal-close" onclick="closeLoginModal()" aria-label="Close">&times;</button>
        </div>

        <form method="POST" action="{{ route('login') }}" class="auth-modal-form" id="loginform">
            @csrf
            <input type="hidden" name="from_modal" value="1">

            <!-- Session Status -->
            @if (session('status'))
                <div class="auth-message">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="auth-message auth-error">
                    @foreach ($errors->all() as $error)
                        <p style="margin: 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Email Address -->
            <div class="auth-form-group">
                <label for="modal-email">Email address</label>
                <input id="modal-email" type="email" name="email" class="auth-form-input" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>

            <!-- Password -->
            <div class="auth-form-group">
                <label for="modal-password">Password</label>
                <input id="modal-password" type="password" name="password" class="auth-form-input" required autocomplete="current-password">
            </div>

            <!-- Remember Me -->
            <div class="auth-checkbox-group">
                <label for="modal-remember_me">
                    <input id="modal-remember_me" type="checkbox" name="remember">
                    Remember Me
                </label>
            </div>

            <!-- Submit Button -->
            <div class="auth-submit">
                <button type="submit" class="auth-button auth-button-primary">Log In</button>
            </div>
        </form>

        <!-- Links -->
        <div class="auth-links">
            @if (Route::has('password.request'))
                <p><a href="{{ route('password.request') }}">Lost your password?</a></p>
            @endif
            @if (Route::has('register'))
                <p><a href="{{ route('register') }}">Please register.</a></p>
            @endif
        </div>

        <!-- Demo Credentials - Hidden in modal -->
        {{-- Demo credentials are not shown in the modal to keep it clean --}}
    </div>
</div>

<style>
/* Login Modal Styling - Matching Share Modal */
.auth-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.auth-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}

.auth-modal-content {
    position: relative;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-width: 450px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 2;
}

.auth-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e0e0e0;
    background: #1d496a;
}

.auth-modal-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #ffffff!important;
}

.auth-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}

.auth-modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.auth-modal-form {
    padding: 1.5rem;
}

.auth-form-group {
    margin-bottom: 1.25rem;
}

.auth-form-group label {
    color: #1d2327;
    font-size: 0.875rem;
    line-height: 1.5;
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.auth-form-input {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    color: #2c3338;
    font-size: 1rem;
    width: 100%;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    line-height: 1.5;
    margin: 0;
    padding: 0.75rem 1rem;
    box-sizing: border-box;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.auth-form-input:focus {
    border-color: #1d496a;
    box-shadow: 0 0 0 3px rgba(29, 73, 106, 0.1);
    outline: none;
}

.auth-checkbox-group {
    margin: 16px 0;
}

.auth-checkbox-group label {
    font-weight: 400;
    display: flex;
    align-items: center;
    margin-bottom: 0;
    cursor: pointer;
    font-size: 14px;
    color: #2c3338;
}

.auth-checkbox-group input[type="checkbox"] {
    margin-right: 6px;
    margin-top: 0;
}

.auth-button {
    display: inline-block;
    text-decoration: none;
    font-size: 1rem;
    line-height: 1.5;
    margin: 0;
    padding: 0.75rem 1.5rem;
    cursor: pointer;
    border: none;
    border-radius: 6px;
    white-space: nowrap;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    font-weight: 500;
    transition: background-color 0.15s ease-in-out, transform 0.1s ease-in-out;
    width: 100%;
}

.auth-button-primary {
    background: #1d496a;
    color: #fff;
}

.auth-button-primary:hover {
    background: #0f2f46;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.auth-button-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.auth-button-primary:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(29, 73, 106, 0.3);
}

.auth-submit {
    margin-top: 1.5rem;
}

.auth-links {
    padding: 0 1.5rem 1.5rem;
    text-align: center;
}

.auth-links p {
    margin: 0.75rem 0 0;
    font-size: 0.875rem;
}

.auth-links a {
    color: #1d496a;
    text-decoration: none;
    font-weight: 500;
}

.auth-links a:hover,
.auth-links a:active {
    color: #0f2f46;
    text-decoration: underline;
}

.auth-message {
    border-left: 4px solid #00a32a;
    padding: 1rem;
    margin-bottom: 1.25rem;
    background: #f0f9ff;
    border-radius: 6px;
    word-wrap: break-word;
    font-size: 0.875rem;
    color: #1d2327;
}

.auth-error {
    border-left: 4px solid #d63638;
    background: #fff5f5;
}

.auth-demo-credentials {
    background: #fffbf0;
    border: 1px solid #f0c947;
    border-left: 4px solid #f0c947;
    padding: 1.25rem;
    margin: 0 1.5rem 1.5rem;
    border-radius: 6px;
    font-size: 0.875rem;
}

.auth-demo-credentials h3 {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1d2327;
}

.auth-demo-credentials ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.auth-demo-credentials li {
    margin-bottom: 0.75rem;
    padding: 0.75rem;
    background: #fff;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
    transition: box-shadow 0.15s ease-in-out;
}

.auth-demo-credentials li:hover {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.auth-demo-credentials li:last-child {
    margin-bottom: 0;
}

.auth-demo-credentials .user-role {
    font-weight: 600;
    color: #1d2327;
    display: block;
    margin-bottom: 0.5rem;
}

.auth-demo-credentials .user-email {
    color: #2c3338;
    font-family: Consolas, Monaco, monospace;
    font-size: 0.8125rem;
    display: block;
    margin-bottom: 0.25rem;
}

.auth-demo-credentials .user-password {
    color: #666;
    font-family: Consolas, Monaco, monospace;
    font-size: 0.8125rem;
    display: block;
}

.auth-demo-credentials .badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 4px;
    margin-left: 0.5rem;
}

.auth-demo-credentials .badge-admin {
    background: #d63638;
    color: #fff;
}

.auth-demo-credentials .badge-user {
    background: #1d496a;
    color: #fff;
}

/* Responsive design */
@media screen and (max-width: 782px) {
    .auth-modal-content {
        margin: 1rem;
        max-width: 100%;
    }

    .auth-modal-header {
        padding: 1.25rem;
    }

    .auth-modal-form,
    .auth-links {
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }

    .auth-demo-credentials {
        margin-left: 1.25rem;
        margin-right: 1.25rem;
    }
}
</style>

<script>
function openLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Focus on first input
        setTimeout(() => {
            const firstInput = modal.querySelector('#modal-email');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    }
}

function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLoginModal();
    }
});

// Handle login link clicks
document.addEventListener('DOMContentLoaded', function() {
    // Intercept all login links
    document.querySelectorAll('a[href*="/login"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            openLoginModal();
        });
    });

    // Auto-open modal if there are errors from modal submission
    @if($shouldAutoOpen)
        document.body.style.overflow = 'hidden';
    @endif
});
</script>
