<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Routes that should be accessible without email verification
     *
     * @var array
     */
    protected $except = [
        'verification.notice',
        'verification.verify',
        'verification.send',
        'logout',
        'password.request',
        'password.email',
        'password.reset',
        'password.update',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectToRoute
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        // Skip if user is not authenticated
        if (!$request->user()) {
            return $next($request);
        }

        // Skip if the user is already verified
        if ($request->user()->hasVerifiedEmail()) {
            return $next($request);
        }

        // Skip if the current route is in the except array
        if (in_array(Route::currentRouteName(), $this->except)) {
            return $next($request);
        }

        // For API requests, return a JSON response
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your email address is not verified.'], 403);
        }

        // Redirect to email verification notice
        return $request->is('email/*') || $request->is('email')
            ? $next($request)
            : redirect()->route('verification.notice');
    }
}
