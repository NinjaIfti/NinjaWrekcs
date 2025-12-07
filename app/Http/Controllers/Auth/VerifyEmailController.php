<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            return $user->isAdmin() 
                ? redirect()->route('admin.dashboard', ['verified' => 1])
                : redirect()->route('profile.index', ['verified' => 1]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $user->isAdmin() 
            ? redirect()->route('admin.dashboard', ['verified' => 1])
            : redirect()->route('profile.index', ['verified' => 1]);
    }
}
