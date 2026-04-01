<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log API requests to avoid spamming the logs with internal calls
        if ($request->is('api/*')) {
            $userAgent = $request->header('User-Agent');
            $browser = 'Unknown';

            if (preg_match('/Edg/i', $userAgent)) {
                $browser = 'Edge';
            } elseif (preg_match('/Firefox/i', $userAgent)) {
                $browser = 'Firefox';
            } elseif (preg_match('/Chrome/i', $userAgent)) {
                $browser = 'Chrome';
            } elseif (preg_match('/Safari/i', $userAgent)) {
                $browser = 'Safari';
            } elseif (preg_match('/Opera/i', $userAgent) || preg_match('/OPR/i', $userAgent)) {
                $browser = 'Opera';
            } elseif (preg_match('/PostmanRuntime/i', $userAgent)) {
                $browser = 'Postman';
            }

            try {
                \App\Models\ActivityLog::create([
                    'user_id' => auth('staff')->id(), // null if not logged in
                    'url' => $request->path(),
                    'method' => $request->method(),
                    'user_agent' => $userAgent,
                    'browser' => $browser
                ]);
            } catch (\Exception $e) {
                // Silently bypass if logging fails to prevent breaking the application
                \Illuminate\Support\Facades\Log::error('Failed to log activity: ' . $e->getMessage());
            }
        }

        return $response;
    }
}
