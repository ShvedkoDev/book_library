<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        // Get demo users for development/testing
        $demoUsers = User::whereIn('email', [
            'admin@micronesianlib.edu',
            'maria.teacher@example.com',
            'john.educator@example.com',
            'sarah.prof@uog.edu',
            'james.lib@fsmgov.org'
        ])->get(['name', 'email', 'role']);

        // If a redirect parameter is provided, store it as the intended URL
        if ($request->has('redirect')) {
            $request->session()->put('url.intended', $request->input('redirect'));
        }

        return view('auth.login', compact('demoUsers'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('library.index', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
