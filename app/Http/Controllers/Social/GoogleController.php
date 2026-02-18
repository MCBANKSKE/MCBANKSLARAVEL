<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    /**
     * Redirect to Google for authentication.
     */
    public function redirect()
    {
        // Check if Google is configured
        if (!$this->socialAuthService->isProviderConfigured('google')) {
            return redirect()->route('login')
                ->with('error', 'Google authentication is not configured.');
        }

        // Store intended URL for redirect after login
        session(['url.intended' => request()->get('redirect', route('profile.show'))]);

        // Configure Google OAuth scopes
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'consent'])
            ->redirect();
    }

    /**
     * Handle callback from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Validate that we have required data
            if (!$googleUser->getEmail()) {
                Log::error("Google login failed: No email provided", [
                    'provider' => 'google',
                    'google_id' => $googleUser->getId(),
                ]);

                return redirect()->route('login')
                    ->with('error', 'Google account must have an email address. Please check your Google account settings.');
            }

            // Verify email is verified in Google account
            if (!$googleUser->user['email_verified'] ?? false) {
                Log::error("Google login failed: Email not verified", [
                    'provider' => 'google',
                    'google_id' => $googleUser->getId(),
                    'email' => $googleUser->getEmail(),
                ]);

                return redirect()->route('login')
                    ->with('error', 'Please verify your email address in your Google account before using it to login.');
            }

            $user = $this->socialAuthService->handleSocialLogin('google', $googleUser);

            // Redirect to intended URL or profile
            $redirectUrl = session()->pull('url.intended', route('profile.show'));

            return redirect($redirectUrl)->with('success', 
                'Successfully logged in with Google!');

        } catch (\Exception $e) {
            Log::error("Google login failed", [
                'error' => $e->getMessage(),
                'provider' => 'google',
            ]);

            return redirect()->route('login')
                ->with('error', 'Failed to login with Google. Please try again.');
        }
    }

    /**
     * Link Google account to existing user.
     */
    public function link()
    {
        if (!$this->socialAuthService->isProviderConfigured('google')) {
            return redirect()->route('profile.edit')
                ->with('error', 'Google authentication is not configured.');
        }

        session(['linking_google' => true]);

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'consent'])
            ->redirect();
    }

    /**
     * Handle Google linking callback.
     */
    public function linkCallback()
    {
        try {
            // Verify this is a linking session
            if (!session('linking_google')) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Invalid linking session. Please try again.');
            }

            $googleUser = Socialite::driver('google')->user();
            $user = Auth::user();

            // Validate email requirement
            if (!$googleUser->getEmail()) {
                session()->forget('linking_google');
                return redirect()->route('profile.edit')
                    ->with('error', 'Google account must have an email address to link.');
            }

            // Verify email is verified in Google account
            if (!$googleUser->user['email_verified'] ?? false) {
                session()->forget('linking_google');
                return redirect()->route('profile.edit')
                    ->with('error', 'Please verify your email address in your Google account before linking it.');
            }

            // Check if this Google account is already linked to the current user
            if ($user->hasSocialAccount('google')) {
                session()->forget('linking_google');
                return redirect()->route('profile.edit')
                    ->with('error', 'This Google account is already linked to your profile.');
            }

            // Check if Google account is linked to another user
            $existingUser = User::findBySocialAccount('google', $googleUser->getId());
            if ($existingUser && $existingUser->id !== $user->id) {
                Log::warning("Google account linking attempt to already linked account", [
                    'provider' => 'google',
                    'google_id' => $googleUser->getId(),
                    'attempting_user_id' => $user->id,
                    'existing_user_id' => $existingUser->id,
                ]);
                
                session()->forget('linking_google');
                return redirect()->route('profile.edit')
                    ->with('error', 'This Google account is already linked to another user.');
            }

            // Link the account
            $this->socialAuthService->linkSocialAccount($user, 'google', $googleUser);

            // Clear linking session
            session()->forget('linking_google');

            return redirect()->route('profile.edit')
                ->with('success', 'Successfully linked Google account!');

        } catch (\Exception $e) {
            Log::error("Google account linking failed", [
                'error' => $e->getMessage(),
                'provider' => 'google',
                'user_id' => Auth::id(),
            ]);

            // Clear linking session on error
            session()->forget('linking_google');

            return redirect()->route('profile.edit')
                ->with('error', 'Failed to link Google account. Please try again.');
        }
    }

    /**
     * Disconnect Google account.
     */
    public function disconnect(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasSocialAccount('google')) {
                return redirect()->back()
                    ->with('error', 'No Google account connected to your profile.');
            }

            // Additional security: Ensure the Google account belongs to the authenticated user
            $socialAccount = $user->socialAccounts()->byProvider('google')->first();
            if (!$socialAccount || $socialAccount->user_id !== $user->id) {
                return redirect()->back()
                    ->with('error', 'Unauthorized action. You can only disconnect your own Google account.');
            }

            $this->socialAuthService->disconnectSocialAccount($user, 'google');

            return redirect()->back()
                ->with('success', 'Successfully disconnected Google account.');

        } catch (\Exception $e) {
            Log::error("Google account disconnect failed", [
                'error' => $e->getMessage(),
                'provider' => 'google',
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to disconnect Google account. Please try again.');
        }
    }
}
