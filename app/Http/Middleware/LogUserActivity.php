<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log authenticated user activities
        if (Auth::check()) {
            $this->logUserActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log user activity based on the request.
     */
    protected function logUserActivity(Request $request, $response): void
    {
        $user = Auth::user();
        $route = $request->route();
        
        if (!$route) {
            return;
        }

        $routeName = $route->getName();
        $method = $request->method();

        // Determine the action based on route name and method
        $action = $this->getActionFromRoute($routeName, $method);

        if (!$action) {
            return;
        }

        // Log the activity
        $this->auditService->log([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $this->getActionDescription($action, $request),
            'metadata' => $this->getActionMetadata($request, $response),
            'level' => $this->getActionLevel($action, $response->getStatusCode()),
        ]);
    }

    /**
     * Determine the audit action from the route.
     */
    protected function getActionFromRoute(?string $routeName, string $method): ?string
    {
        if (!$routeName) {
            return null;
        }

        // Authentication actions
        if ($routeName === 'central.login.post') {
            return 'login';
        }

        if ($routeName === 'central.logout') {
            return 'logout';
        }

        if ($routeName === 'password.request') {
            return 'password_reset_request';
        }

        if ($routeName === 'password.update') {
            return 'password_reset_completed';
        }

        // Profile actions
        if (str_starts_with($routeName, 'profile.')) {
            return match($routeName) {
                'profile.show' => 'profile_viewed',
                'profile.edit' => 'profile_edit_viewed',
                default => $this->getProfileAction($method),
            };
        }

        // Two-factor authentication actions
        if (str_starts_with($routeName, '2fa.')) {
            return match($routeName) {
                '2fa.verify' => 'two_factor_verified',
                '2fa.recovery.verify' => 'recovery_code_used',
                '2fa.logout' => 'logout',
                default => null,
            };
        }

        // Social authentication actions
        if (str_starts_with($routeName, 'central.google') || str_starts_with($routeName, 'central.github')) {
            return 'social_login';
        }

        // Admin actions
        if (str_starts_with($routeName, 'admin.')) {
            return 'admin_action';
        }

        // API actions
        if ($request->is('api/*')) {
            return 'api_request';
        }

        return null;
    }

    /**
     * Get profile action based on HTTP method.
     */
    protected function getProfileAction(string $method): string
    {
        return match($method) {
            'PUT', 'PATCH' => 'profile_updated',
            'POST' => 'profile_created',
            'DELETE' => 'profile_deleted',
            default => 'profile_accessed',
        };
    }

    /**
     * Get human-readable description for the action.
     */
    protected function getActionDescription(string $action, Request $request): string
    {
        return match($action) {
            'login' => 'User logged in',
            'logout' => 'User logged out',
            'profile_viewed' => 'Viewed profile',
            'profile_updated' => 'Updated profile',
            'profile_edit_viewed' => 'Accessed profile edit form',
            'password_reset_request' => 'Requested password reset',
            'password_reset_completed' => 'Completed password reset',
            'two_factor_verified' => 'Two-factor authentication verified',
            'recovery_code_used' => 'Used recovery code',
            'social_login' => 'Logged in with social account',
            'admin_action' => 'Performed admin action',
            'api_request' => 'Made API request',
            default => 'User activity: ' . $action,
        };
    }

    /**
     * Get metadata for the action.
     */
    protected function getActionMetadata(Request $request, $response): array
    {
        $metadata = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
        ];

        // Add route information
        if ($route = $request->route()) {
            $metadata['route_name'] = $route->getName();
            $metadata['route_parameters'] = $route->parameters();
        }

        // Add request size for large requests
        if ($request->header('Content-Length')) {
            $metadata['content_length'] = $request->header('Content-Length');
        }

        // Add response size
        if (method_exists($response, 'getOriginalContent')) {
            $metadata['response_size'] = strlen($response->getOriginalContent());
        }

        return $metadata;
    }

    /**
     * Determine the log level based on action and response status.
     */
    protected function getActionLevel(string $action, int $statusCode): string
    {
        // Error responses
        if ($statusCode >= 400) {
            return match(true) {
                $statusCode >= 500 => 'error',
                $statusCode === 429 => 'warning',
                $statusCode === 403 => 'warning',
                $statusCode === 401 => 'warning',
                default => 'info',
            };
        }

        // Security-sensitive actions
        if (in_array($action, [
            'login',
            'logout',
            'password_reset_request',
            'password_reset_completed',
            'two_factor_verified',
            'recovery_code_used',
        ])) {
            return 'info';
        }

        // Admin actions
        if (str_starts_with($action, 'admin_')) {
            return 'warning';
        }

        return 'info';
    }
}
