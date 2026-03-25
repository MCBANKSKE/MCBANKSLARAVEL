<?php

namespace App\Http\Middleware;

use App\Services\RateLimitingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class RateLimitAuth
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

        if ($this->rateLimitingService->checkAuthRateLimit($request)) {
            $this->rateLimitingService->incrementRateLimitStats('auth');
            
            return Response::json([
                'message' => 'Too many authentication attempts. Please try again later.',
                'retry_after' => 60,
            ], 429);
        }

        $response = $next($request);

        // Add rate limit headers
        $headers = $this->rateLimitingService->getRateLimitHeaders($request, 'auth', 5, 1);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
