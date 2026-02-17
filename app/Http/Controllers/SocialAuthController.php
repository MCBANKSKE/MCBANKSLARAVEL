<?php

namespace App\Http\Controllers;

use App\Services\SocialAuthService;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    /**
     * Redirect to social provider.
     */
    public function redirect(string $provider)
    {
        // Check if provider is configured
        if (!$this->socialAuthService->isProviderConfigured($provider)) {
            return redirect()->route('login')
                ->with('error', "Social provider '{$provider}' is not configured.");
        }

        // Store intended URL for redirect after login
        session(['url.intended' => request()->get('redirect', route('profile.show'))]);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle callback from social provider.
     */
    public function callback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = $this->socialAuthService->handleSocialLogin($provider, $socialUser);

            // Redirect to intended URL or profile
            $redirectUrl = session()->pull('url.intended', route('profile.show'));

            return redirect($redirectUrl)->with('success', 
                "Successfully logged in with " . ucfirst($provider) . "!");

        } catch (\Exception $e) {
            Log::error("Social login failed for {$provider}", [
                'error' => $e->getMessage(),
                'provider' => $provider,
            ]);

            return redirect()->route('login')
                ->with('error', "Failed to login with {$provider}. Please try again.");
        }
    }

    /**
     * Disconnect social account.
     */
    public function disconnect(Request $request, string $provider)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasSocialAccount($provider)) {
                return redirect()->back()
                    ->with('error', "No {$provider} account connected to your profile.");
            }

            // Additional security: Ensure the social account belongs to the authenticated user
            $socialAccount = $user->socialAccounts()->byProvider($provider)->first();
            if (!$socialAccount || $socialAccount->user_id !== $user->id) {
                return redirect()->back()
                    ->with('error', "Unauthorized action. You can only disconnect your own social accounts.");
            }

            $this->socialAuthService->disconnectSocialAccount($user, $provider);

            return redirect()->back()
                ->with('success', "Successfully disconnected {$provider} account.");

        } catch (\Exception $e) {
            Log::error("Social account disconnect failed for {$provider}", [
                'error' => $e->getMessage(),
                'provider' => $provider,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', "Failed to disconnect {$provider} account. Please try again.");
        }
    }

    /**
     * Link social account to existing user.
     */
    public function link(string $provider)
    {
        if (!$this->socialAuthService->isProviderConfigured($provider)) {
            return redirect()->route('profile.edit')
                ->with('error', "Social provider '{$provider}' is not configured.");
        }

        session(['linking_social' => true]);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle linking callback.
     */
    public function linkCallback(string $provider)
    {
        try {
            // Verify this is a linking session
            if (!session('linking_social')) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Invalid linking session. Please try again.');
            }

            $socialUser = Socialite::driver($provider)->user();
            $user = Auth::user();

            // Check if this social account is already linked to the current user
            if ($user->hasSocialAccount($provider)) {
                return redirect()->route('profile.edit')
                    ->with('error', "This {$provider} account is already linked to your profile.");
            }

            // Check if social account is linked to another user
            $existingUser = User::findBySocialAccount($provider, $socialUser->getId());
            if ($existingUser && $existingUser->id !== $user->id) {
                Log::warning("Social account linking attempt to already linked account", [
                    'provider' => $provider,
                    'social_user_id' => $socialUser->getId(),
                    'attempting_user_id' => $user->id,
                    'existing_user_id' => $existingUser->id,
                ]);
                
                return redirect()->route('profile.edit')
                    ->with('error', "This {$provider} account is already linked to another user.");
            }

            // Link the account
            $this->socialAuthService->linkSocialAccount($user, $provider, $socialUser);

            // Clear linking session
            session()->forget('linking_social');

            return redirect()->route('profile.edit')
                ->with('success', "Successfully linked {$provider} account!");

        } catch (\Exception $e) {
            Log::error("Social account linking failed for {$provider}", [
                'error' => $e->getMessage(),
                'provider' => $provider,
                'user_id' => Auth::id(),
            ]);

            // Clear linking session on error
            session()->forget('linking_social');

            return redirect()->route('profile.edit')
                ->with('error', "Failed to link {$provider} account. Please try again.");
        }
    }

    /**
     * Get available social providers for API.
     */
    public function providers()
    {
        $providers = $this->socialAuthService->getAvailableProviders();
        $configured = [];

        foreach ($providers as $key => $provider) {
            $configured[$key] = array_merge($provider, [
                'configured' => $this->socialAuthService->isProviderConfigured($key),
            ]);
        }

        return response()->json($configured);
    }
}
