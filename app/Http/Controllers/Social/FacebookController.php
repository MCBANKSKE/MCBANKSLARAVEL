<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    /**
     * Redirect to Facebook for authentication.
     */
    public function redirect()
    {
        // Check if Facebook is configured
        if (!$this->socialAuthService->isProviderConfigured('facebook')) {
            return redirect()->route('login')
                ->with('error', 'Facebook authentication is not configured.');
        }

        // Store intended URL for redirect after login
        session(['url.intended' => request()->get('redirect', route('profile.show'))]);

        // Configure Facebook OAuth scopes
        return Socialite::driver('facebook')
            ->scopes(['email', 'public_profile'])
            ->redirect();
    }

    /**
     * Handle callback from Facebook.
     */
    public function callback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            
            // Validate that we have required data
            if (!$facebookUser->getEmail()) {
                Log::error("Facebook login failed: No email provided", [
                    'provider' => 'facebook',
                    'facebook_id' => $facebookUser->getId(),
                ]);

                return redirect()->route('login')
                    ->with('error', 'Facebook account must have an email address. Please check your Facebook privacy settings.');
            }

            $user = $this->socialAuthService->handleSocialLogin('facebook', $facebookUser);

            // Redirect to intended URL or profile
            $redirectUrl = session()->pull('url.intended', route('profile.show'));

            return redirect($redirectUrl)->with('success', 
                'Successfully logged in with Facebook!');

        } catch (\Exception $e) {
            Log::error("Facebook login failed", [
                'error' => $e->getMessage(),
                'provider' => 'facebook',
            ]);

            return redirect()->route('login')
                ->with('error', 'Failed to login with Facebook. Please try again.');
        }
    }

    /**
     * Link Facebook account to existing user.
     */
    public function link()
    {
        if (!$this->socialAuthService->isProviderConfigured('facebook')) {
            return redirect()->route('profile.edit')
                ->with('error', 'Facebook authentication is not configured.');
        }

        session(['linking_facebook' => true]);

        return Socialite::driver('facebook')
            ->scopes(['email', 'public_profile'])
            ->redirect();
    }

    /**
     * Handle Facebook linking callback.
     */
    public function linkCallback()
    {
        try {
            // Verify this is a linking session
            if (!session('linking_facebook')) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Invalid linking session. Please try again.');
            }

            $facebookUser = Socialite::driver('facebook')->user();
            $user = Auth::user();

            // Validate email requirement
            if (!$facebookUser->getEmail()) {
                session()->forget('linking_facebook');
                return redirect()->route('profile.edit')
                    ->with('error', 'Facebook account must have an email address to link.');
            }

            // Check if this Facebook account is already linked to the current user
            if ($user->hasSocialAccount('facebook')) {
                session()->forget('linking_facebook');
                return redirect()->route('profile.edit')
                    ->with('error', 'This Facebook account is already linked to your profile.');
            }

            // Check if Facebook account is linked to another user
            $existingUser = User::findBySocialAccount('facebook', $facebookUser->getId());
            if ($existingUser && $existingUser->id !== $user->id) {
                Log::warning("Facebook account linking attempt to already linked account", [
                    'provider' => 'facebook',
                    'facebook_id' => $facebookUser->getId(),
                    'attempting_user_id' => $user->id,
                    'existing_user_id' => $existingUser->id,
                ]);
                
                session()->forget('linking_facebook');
                return redirect()->route('profile.edit')
                    ->with('error', 'This Facebook account is already linked to another user.');
            }

            // Link the account
            $this->socialAuthService->linkSocialAccount($user, 'facebook', $facebookUser);

            // Clear linking session
            session()->forget('linking_facebook');

            return redirect()->route('profile.edit')
                ->with('success', 'Successfully linked Facebook account!');

        } catch (\Exception $e) {
            Log::error("Facebook account linking failed", [
                'error' => $e->getMessage(),
                'provider' => 'facebook',
                'user_id' => Auth::id(),
            ]);

            // Clear linking session on error
            session()->forget('linking_facebook');

            return redirect()->route('profile.edit')
                ->with('error', 'Failed to link Facebook account. Please try again.');
        }
    }

    /**
     * Disconnect Facebook account.
     */
    public function disconnect(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasSocialAccount('facebook')) {
                return redirect()->back()
                    ->with('error', 'No Facebook account connected to your profile.');
            }

            // Additional security: Ensure the Facebook account belongs to the authenticated user
            $socialAccount = $user->socialAccounts()->byProvider('facebook')->first();
            if (!$socialAccount || $socialAccount->user_id !== $user->id) {
                return redirect()->back()
                    ->with('error', 'Unauthorized action. You can only disconnect your own Facebook account.');
            }

            $this->socialAuthService->disconnectSocialAccount($user, 'facebook');

            return redirect()->back()
                ->with('success', 'Successfully disconnected Facebook account.');

        } catch (\Exception $e) {
            Log::error("Facebook account disconnect failed", [
                'error' => $e->getMessage(),
                'provider' => 'facebook',
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to disconnect Facebook account. Please try again.');
        }
    }
}
