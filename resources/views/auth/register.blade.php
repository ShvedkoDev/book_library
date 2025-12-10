@section('title', 'Registration - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('page_title', 'Registration')

<x-guest-layout>
    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="login-message login-error">
            @foreach ($errors->all() as $error)
                <p style="margin: 0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="login-form" id="registerform">
        @csrf

        <!-- Name -->
        <div class="form-group">
            <label for="name">Full name</label>
            <input id="name" type="text" name="name" class="form-input" value="{{ old('name') }}" required autofocus autocomplete="name">
        </div>

        <!-- Email Address -->
        <div class="form-group">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}" required autocomplete="username">
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" class="form-input" required autocomplete="new-password">
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" required autocomplete="new-password">
        </div>

        <!-- Submit Button -->
        <div class="login-submit">
            <input type="submit" class="button button-primary button-large" value="Register">
        </div>
    </form>

    <!-- Links -->
    <div class="login-links">
        <p><a href="{{ route('login') }}">Already registered? Log in</a></p>
    </div>
</x-guest-layout>
