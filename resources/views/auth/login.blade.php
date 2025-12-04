@section('title', 'Login - Micronesian Teachers Digital Library')

<x-guest-layout>
    <form method="POST" action="{{ route('login') }}" class="login-form" id="loginform">
        @csrf

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

        <!-- Email Address -->
        <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" class="form-input" required autocomplete="current-password">
        </div>

        <!-- Remember Me -->
        <div class="checkbox-group">
            <label for="remember_me">
                <input id="remember_me" type="checkbox" name="remember">
                Remember Me
            </label>
        </div>

        <!-- Submit Button -->
        <div class="login-submit">
            <input type="submit" class="button button-primary button-large" value="Log In">
        </div>
    </form>

    <!-- Links -->
    <div class="login-links">
        @if (Route::has('password.request'))
            <p><a href="{{ route('password.request') }}">Lost your password?</a></p>
        @endif
        @if (Route::has('register'))
            <p><a href="{{ route('register') }}">Don't have an account? Register</a></p>
        @endif
    </div>

    <!-- Demo Credentials -->
    @if(isset($demoUsers) && $demoUsers->isNotEmpty())
        <div class="demo-credentials">
            <h3>Demo Login Credentials</h3>
            <ul>
                @foreach($demoUsers as $user)
                    <li>
                        <span class="user-role">
                            {{ $user->name }}
                            <span class="badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                        </span>
                        <span class="user-email">{{ $user->email }}</span>
                        <span class="user-password">password123</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</x-guest-layout>
