<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $ttl = 3600): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Generate cache key based on full URL
        $cacheKey = 'response_' . md5($request->fullUrl());

        return Cache::remember($cacheKey, $ttl, function () use ($next, $request) {
            $response = $next($request);
            
            // Only cache successful responses
            if ($response->getStatusCode() === 200) {
                return $response;
            }
            
            return $response;
        });
    }
}
