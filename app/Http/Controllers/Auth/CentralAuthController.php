<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;

class CentralAuthController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    /**
     * Show the centralized login page with role options.
     */
    public function showLoginForm()
    {
        // If user is already authenticated, redirect them
        if (Auth::check()) {
            return redirect()->intended($this->getRoleBasedRedirect(Auth::user()));
        }

        return view('auth.central-login');
    }

    /**
     * Show the centralized registration page with role selection.
     */
    public function showRegistrationForm()
    {
        // If user is already authenticated, redirect them
        if (Auth::check()) {
            return redirect()->intended($this->getRoleBasedRedirect(Auth::user()));
        }

        return view('auth.central-register');
    }

    /**
     * Handle centralized login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            $redirectUrl = $this->getRoleBasedRedirect($user);

            return redirect()->intended($redirectUrl)
                ->with('success', 'Successfully logged in!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('password'));
    }

    /**
     * Handle centralized registration with role assignment.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'role' => ['required', 'string', 'in:guest,host,admin'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => ($request->role === 'admin') ? now() : null, // Only admins auto-verified
        ]);

        // Assign the selected role
        $user->assignRole($request->role);

        // Create user profile
        $profile = $user->getOrCreateProfile();
        $profile->bio = "Joined as {$request->role}";
        $profile->updateCompletionPercentage();

        // Log in the new user
        Auth::login($user);

        // Send verification email if not verified
        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        $redirectUrl = $this->getRoleBasedRedirect($user);

        return redirect($redirectUrl)
            ->with('success', 'Registration successful! Please check your email for verification.');
    }

    /**
     * Handle Google OAuth redirect with role selection.
     */
    public function googleRedirect(Request $request)
    {
        // Check if Google is configured
        if (!$this->socialAuthService->isProviderConfigured('google')) {
            return back()
                ->with('error', 'Google authentication is not configured.');
        }

        // Store role preference in session if provided
        if ($request->has('role')) {
            session(['oauth_role' => $request->role]);
        }

        // Configure Google OAuth scopes
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'consent'])
            ->redirect();
    }

    /**
     * Handle Google OAuth callback with role assignment.
     */
    public function googleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Validate that we have required data
            if (!$googleUser->getEmail()) {
                return redirect()->route('central.login')
                    ->with('error', 'Google account must have an email address.');
            }

            // Verify email is verified in Google account
            if (!$googleUser->user['email_verified'] ?? false) {
                return redirect()->route('central.login')
                    ->with('error', 'Please verify your email address in your Google account.');
            }

            // Check if user exists
            $existingUser = User::where('email', $googleUser->getEmail())->first();
            
            if ($existingUser) {
                // User exists, log them in
                Auth::login($existingUser, true);
                $redirectUrl = $this->getRoleBasedRedirect($existingUser);
                
                return redirect($redirectUrl)
                    ->with('success', 'Successfully logged in with Google!');
            }

            // New user - create account with role from session or default to guest
            $role = session('oauth_role', 'guest');
            session()->forget('oauth_role');

            $user = $this->createUserFromGoogle($googleUser, $role);
            Auth::login($user, true);

            // Send verification email if not verified and not admin
            if (!$user->hasVerifiedEmail() && !$user->hasRole('admin')) {
                $user->sendEmailVerificationNotification();
            }

            $redirectUrl = $this->getRoleBasedRedirect($user);

            return redirect($redirectUrl)
                ->with('success', 'Successfully registered and logged in with Google! Please check your email for verification.');

        } catch (\Exception $e) {
            return redirect()->route('central.login')
                ->with('error', 'Failed to login with Google. Please try again.');
        }
    }

    /**
     * Create user from Google OAuth data.
     */
    private function createUserFromGoogle($googleUser, string $role): User
    {
        $user = User::create([
            'name' => $googleUser->getName() ?: $googleUser->getEmail(),
            'email' => $googleUser->getEmail(),
            'password' => bcrypt(\Str::random(32)),
            'email_verified_at' => ($role === 'admin') ? now() : null, // Only admins auto-verified
        ]);

        // Assign role
        $user->assignRole($role);

        // Create social account
        $user->socialAccounts()->create([
            'provider' => 'google',
            'provider_id' => $googleUser->getId(),
            'provider_token' => $googleUser->token,
            'provider_refresh_token' => $googleUser->refreshToken,
            'provider_expires_in' => $googleUser->expiresIn ? now()->addSeconds($googleUser->expiresIn)->timestamp : null,
            'provider_data' => $googleUser->user,
            'nickname' => $googleUser->getNickname(),
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        // Create profile
        $profile = $user->getOrCreateProfile();
        $profile->bio = "Joined via Google as {$role}";
        $profile->updateCompletionPercentage();

        return $user;
    }

    /**
     * Get role-based redirect URL.
     */
    private function getRoleBasedRedirect(User $user): string
    {
        // Check email verification for non-admin users
        if (is_null($user->email_verified_at) && !$user->hasRole('admin')) {
            return route('verification.notice');
        }

        // Super admin goes to admin dashboard
        if ($user->is_superadmin) {
            return '/admin';
        }

        // Role-based redirects
        if ($user->hasRole('admin')) {
            return '/admin';
        } elseif ($user->hasRole('host')) {
            return '/host';
        } elseif ($user->hasRole('guest')) {
            return '/guest';
        }

        // Default redirect
        return '/guest';
    }

    /**
     * Show the password reset request form.
     */
    public function showPasswordRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle password reset link request.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We'll send password reset link to the user
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Handle password reset.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('central.login')->with('status', __($status));
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('central.login')
            ->with('status', 'You have been logged out successfully.');
    }
}
