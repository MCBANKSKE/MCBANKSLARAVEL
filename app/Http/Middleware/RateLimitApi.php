<?php

namespace App\Http\Middleware;

use App\Services\RateLimitingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class RateLimitApi
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

        // Get the endpoint from the route name or path
        $endpoint = $this->getEndpointName($request);

        // Check specific endpoint rate limit
        if ($this->rateLimitingService->checkApiEndpointRateLimit($request, $endpoint)) {
            $this->rateLimitingService->incrementRateLimitStats('api_' . $endpoint);
            
            $limits = $this->rateLimitingService->getApiEndpointLimits()[$endpoint] ?? [
                'max_attempts' => 60,
                'decay_minutes' => 60
            ];
            
            return Response::json([
                'message' => 'Too many API requests. Please try again later.',
                'retry_after' => $limits['decay_minutes'] * 60,
                'endpoint' => $endpoint,
            ], 429);
        }

        // Check general API rate limit
        if ($this->rateLimitingService->checkApiRateLimit($request)) {
            $this->rateLimitingService->incrementRateLimitStats('api');
            
            $maxAttempts = $request->user() ? 1000 : 100;
            
            return Response::json([
                'message' => 'Too many API requests. Please try again later.',
                'retry_after' => 3600,
            ], 429);
        }

        $response = $next($request);

        // Add rate limit headers
        $maxAttempts = $request->user() ? 1000 : 100;
        $headers = $this->rateLimitingService->getRateLimitHeaders($request, 'api', $maxAttempts, 60);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    /**
     * Get the endpoint name from the request.
     */
    protected function getEndpointName(Request $request): string
    {
        $routeName = $request->route()?->getName();
        
        if ($routeName) {
            // Convert route name to endpoint format
            return str_replace('.', '_', $routeName);
        }

        // Fallback to path-based endpoint name
        $path = $request->path();
        $segments = explode('/', $path);
        
        // Remove 'api' prefix if present
        if ($segments[0] === 'api') {
            array_shift($segments);
        }
        
        // Create endpoint name from path segments
        return implode('_', array_slice($segments, 0, 3));
    }
}
