<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    const ADMIN_EMAIL = 'ifti3061@gmail.com';

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->email !== self::ADMIN_EMAIL) {
            abort(403, 'Unauthorized access. Admin only.');
        }

        return $next($request);
    }
}


