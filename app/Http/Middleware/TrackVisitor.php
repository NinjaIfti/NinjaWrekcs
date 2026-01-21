<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip tracking for admin routes and API routes
        if ($request->is('admin/*') || $request->is('api/*')) {
            return $next($request);
        }

        // Skip tracking if visitors table doesn't exist (e.g., during tests)
        if (!Schema::hasTable('visitors')) {
            return $next($request);
        }

        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        
        // Parse user agent to get device info
        $deviceInfo = $this->parseUserAgent($userAgent);
        
        // Find or create visitor record
        $visitor = Visitor::where('ip_address', $ipAddress)->first();
        
        if ($visitor) {
            // Update existing visitor
            $visitor->update([
                'user_agent' => $userAgent,
                'device_type' => $deviceInfo['type'],
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'page_visited' => $request->fullUrl(),
                'last_visit_at' => Carbon::now(),
                'visit_count' => $visitor->visit_count + 1,
            ]);
        } else {
            // Create new visitor record
            Visitor::create([
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_type' => $deviceInfo['type'],
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'page_visited' => $request->fullUrl(),
                'last_visit_at' => Carbon::now(),
                'visit_count' => 1,
            ]);
        }

        return $next($request);
    }

    /**
     * Parse user agent to determine device type, browser, and OS
     */
    private function parseUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return [
                'type' => 'Unknown',
                'browser' => 'Unknown',
                'os' => 'Unknown',
            ];
        }
        
        // Determine device type
        $deviceType = 'Desktop';
        if (stripos($userAgent, 'Mobile') !== false || stripos($userAgent, 'Android') !== false || stripos($userAgent, 'iPhone') !== false) {
            $deviceType = 'Mobile';
        } elseif (stripos($userAgent, 'Tablet') !== false || stripos($userAgent, 'iPad') !== false) {
            $deviceType = 'Tablet';
        }
        
        // Determine browser
        $browser = 'Unknown';
        if (stripos($userAgent, 'Chrome') !== false && stripos($userAgent, 'Edg') === false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false) {
            $browser = 'Safari';
        } elseif (stripos($userAgent, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (stripos($userAgent, 'Opera') !== false || stripos($userAgent, 'OPR') !== false) {
            $browser = 'Opera';
        }
        
        // Determine OS
        $os = 'Unknown';
        if (stripos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (stripos($userAgent, 'Mac OS X') !== false || stripos($userAgent, 'Macintosh') !== false) {
            $os = 'macOS';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            $os = 'iOS';
        }
        
        return [
            'type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }
}























