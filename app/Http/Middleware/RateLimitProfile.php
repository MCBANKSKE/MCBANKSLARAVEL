<?php

namespace App\Http\Middleware;

use App\Services\RateLimitingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class RateLimitProfile
{
    protected RateLimitingService $rateLimitingService;

    public function __construct(RateLimitingService $rateLimitingService)
    {
        $this->rateLimitingService = $rateLimitingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if IP is blacklisted
        if ($this->rateLimitingService->isIpBlacklisted($request->ip())) {
            abort(429, 'Your IP address has been temporarily blocked due to suspicious activity.');
        }

        // Different rate limits for different profile actions
        $action = $this->getProfileAction($request);
        
        $rateLimited = false;
        $maxAttempts = 10;
        $decayMinutes = 60;

        switch ($action) {
            case 'update':
                $rateLimited = $this->rateLimitingService->checkProfileUpdateRateLimit($request);
                $maxAttempts = 10;
                $decayMinutes = 60;
                break;
            case 'avatar_upload':
                $rateLimited = $this->rateLimitingService->checkAvatarUploadRateLimit($request);
                $maxAttempts = 5;
                $decayMinutes = 60;
                break;
            case 'view':
                // More lenient for viewing profiles
                $rateLimited = false;
                $maxAttempts = 200;
                $decayMinutes = 60;
                break;
            default:
                // General profile operations
                $rateLimited = $this->rateLimitingService->checkProfileUpdateRateLimit($request);
                break;
        }

        if ($rateLimited) {
            $this->rateLimitingService->incrementRateLimitStats('profile_' . $action);
            
            return Response::json([
                'message' => 'Too many profile update attempts. Please try again later.',
                'retry_after' => $decayMinutes * 60,
                'action' => $action,
            ], 429);
        }

        $response = $next($request);

        // Add rate limit headers
        $key = 'profile_' . $action;
        $headers = $this->rateLimitingService->getRateLimitHeaders($request, $key, $maxAttempts, $decayMinutes);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    /**
     * Determine the profile action from the request.
     */
    protected function getProfileAction(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // Check for avatar upload
        if ($method === 'POST' && str_contains($path, 'avatar')) {
            return 'avatar_upload';
        }

        // Check for profile updates
        if ($method === 'PUT' || $method === 'PATCH') {
            return 'update';
        }

        // Check for profile viewing
        if ($method === 'GET') {
            return 'view';
        }

        return 'general';
    }
}
