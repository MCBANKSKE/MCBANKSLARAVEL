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
                    ->with('error', "No {$provider} account connected.");
            }

            $this->socialAuthService->disconnectSocialAccount($user, $provider);

            return redirect()->back()
                ->with('success', "Successfully disconnected {$provider} account.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
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
            $socialUser = Socialite::driver($provider)->user();
            $user = Auth::user();

            // Check if this social account is already linked to another user
            if ($user->hasSocialAccount($provider)) {
                return redirect()->route('profile.edit')
                    ->with('error', "This {$provider} account is already linked to your profile.");
            }

            // Check if social account is linked to another user
            $existingUser = User::findBySocialAccount($provider, $socialUser->getId());
            if ($existingUser && $existingUser->id !== $user->id) {
                return redirect()->route('profile.edit')
                    ->with('error', "This {$provider} account is already linked to another user.");
            }

            // Link the account
            $this->socialAuthService->linkSocialAccount($user, $provider, $socialUser);

            return redirect()->route('profile.edit')
                ->with('success', "Successfully linked {$provider} account!");

        } catch (\Exception $e) {
            Log::error("Social account linking failed for {$provider}", [
                'error' => $e->getMessage(),
                'provider' => $provider,
                'user_id' => Auth::id(),
            ]);

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
