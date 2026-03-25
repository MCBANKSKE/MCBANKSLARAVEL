<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorChallenge
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Check if user has 2FA enabled and hasn't completed the challenge
        if ($user && $user->hasTwoFactorEnabled() && !Session::has('2fa.verified')) {
            // Store the intended URL for redirect after 2FA verification
            Session::put('2fa.intended', $request->fullUrl());
            
            // Redirect to 2FA challenge page
            return redirect()->route('2fa.challenge');
        }

        return $next($request);
    }
}
