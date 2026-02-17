<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        // If user is already authenticated, redirect them
        if (Auth::check()) {
            return redirect()->intended($this->redirectPath());
        }

        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $this->ensureIsNotRateLimited($request);

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Clear rate limiter on successful login
            RateLimiter::clear($this->throttleKey($request));

            // Log successful login
            activity()
                ->causedBy(Auth::user())
                ->withProperties(['ip' => $request->ip()])
                ->log('User logged in');

            return redirect()->intended($this->redirectPath());
        }

        // Increment rate limiter on failed login
        RateLimiter::hit($this->throttleKey($request), 60); // Lock for 60 seconds

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Log logout if user was authenticated
        if ($user) {
            activity()
                ->causedBy($user)
                ->withProperties(['ip' => $request->ip()])
                ->log('User logged out');
        }

        return redirect('/login')->with('status', 'You have been logged out successfully.');
    }

    /**
     * Get the post-login redirect path.
     */
    protected function redirectPath()
    {
        $user = Auth::user();

        // Super admin goes to admin dashboard
        if ($user->is_superadmin) {
            return '/admin';
        }

        // Check email verification for regular users
        if (is_null($user->email_verified_at)) {
            return route('verification.notice');
        }

        // Role-based redirects
        if ($user->hasRole('admin')) {
            return '/admin';
        } elseif ($user->hasRole('member')) {
            return '/profile';
        }

        // Default redirect
        return '/dashboard';
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }
}
