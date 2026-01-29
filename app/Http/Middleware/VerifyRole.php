<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }
        
        // Allow access if user has the required role
        if ($user->hasRole($role)) {
            return $next($request);
        }

        // Redirect to the appropriate dashboard based on user's role
        if ($user->hasRole('admin')) return redirect('/admin');
        if ($user->hasRole('member')) return redirect('/app');

        // If user has no role, log them out and redirect to login
        Auth::logout();
        return redirect()->route('login')
            ->with('error', 'You do not have permission to access this area.');
    }
}
