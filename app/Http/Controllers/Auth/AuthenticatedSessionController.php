<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        
        // Always redirect admin users to admin dashboard, regardless of intended URL
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Regular users - redirect to profile (or intended URL if it's not admin)
        $intended = $request->session()->pull('url.intended');
        
        // If intended URL is an admin route, redirect to profile instead
        if ($intended && str_contains($intended, '/admin')) {
            return redirect()->route('profile.index');
        }
        
        // Use intended URL if available, otherwise go to profile
        return redirect($intended ?? route('profile.index'));
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
