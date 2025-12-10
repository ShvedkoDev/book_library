@section('title', 'Reset password - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('page_title', 'Reset password')

<x-guest-layout>
    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="login-message login-error">
            @foreach ($errors->all() as $error)
                <p style="margin: 0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" class="login-form">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="form-group">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" class="form-input" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">New password</label>
            <input id="password" type="password" name="password" class="form-input" required autocomplete="new-password">
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" required autocomplete="new-password">
        </div>

        <!-- Submit Button -->
        <div class="login-submit">
            <input type="submit" class="button button-primary button-large" value="Reset password">
        </div>
    </form>
</x-guest-layout>
