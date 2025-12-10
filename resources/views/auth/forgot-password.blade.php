@section('title', 'Forgot password - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('page_title', 'Forgot password?')

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

    <div style="font-size: 1rem;color: #1d2327;line-height: 1.5;padding: 1.5rem;">
        Forgot your password? No problem. Just enter your email address and we will email you a password reset link.
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="login-form">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <!-- Submit Button -->
        <div class="login-submit">
            <input type="submit" class="button button-primary button-large" value="Email password reset link">
        </div>
    </form>

    <!-- Links -->
    <div class="login-links">
        <p><a href="{{ route('login') }}">Back to login</a></p>
    </div>
</x-guest-layout>
