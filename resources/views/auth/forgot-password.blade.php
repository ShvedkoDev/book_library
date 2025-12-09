@section('title', 'Forgot Password - FSM National Vernacular Language Arts (VLA) Curriculum')

<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="login-message">
            {{ session('status') }}
        </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="login-message login-error">
            @foreach ($errors->all() as $error)
                <p style="margin: 0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div style="margin-bottom: 20px; font-size: 14px; color: #1d2327; line-height: 1.5;">
        Forgot your password? No problem. Just enter your email address and we will email you a password reset link.
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="login-form">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <!-- Submit Button -->
        <div class="login-submit">
            <input type="submit" class="button button-primary button-large" value="Email Password Reset Link">
        </div>
    </form>

    <!-- Links -->
    <div class="login-links">
        <p><a href="{{ route('login') }}">Back to Login</a></p>
    </div>
</x-guest-layout>
