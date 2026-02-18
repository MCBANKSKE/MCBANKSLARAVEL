<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GitHubController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    /**
     * Redirect to GitHub for authentication.
     */
    public function redirect()
    {
        // Check if GitHub is configured
        if (!$this->socialAuthService->isProviderConfigured('github')) {
            return redirect()->route('login')
                ->with('error', 'GitHub authentication is not configured.');
        }

        // Store intended URL for redirect after login
        session(['url.intended' => request()->get('redirect', route('profile.show'))]);

        // Configure GitHub OAuth scopes
        return Socialite::driver('github')
            ->scopes(['user:email'])
            ->redirect();
    }

    /**
     * Handle callback from GitHub.
     */
    public function callback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();
            
            // GitHub might not provide email publicly, so we need to handle this
            $email = $githubUser->getEmail();
            
            if (!$email) {
                // Try to get primary email from GitHub API
                try {
                    $emails = Socialite::driver('github')->getHttpClient()->get('https://api.github.com/user/emails', [
                        'headers' => [
                            'Authorization' => 'token ' . $githubUser->token,
                            'Accept' => 'application/vnd.github.v3+json',
                        ],
                    ]);
                    
                    $emailData = json_decode($emails->getBody(), true);
                    $primaryEmail = collect($emailData)->firstWhere('primary', true);
                    $email = $primaryEmail['email'] ?? null;
                } catch (\Exception $emailException) {
                    Log::warning("Could not fetch GitHub primary email", [
                        'github_id' => $githubUser->getId(),
                        'error' => $emailException->getMessage(),
                    ]);
                }
            }

            if (!$email) {
                Log::error("GitHub login failed: No email provided", [
                    'provider' => 'github',
                    'github_id' => $githubUser->getId(),
                ]);

                return redirect()->route('login')
                    ->with('error', 'GitHub account must have a public email address. Please check your GitHub privacy settings or add a public email to your profile.');
            }

            // Update the user object with the fetched email
            $githubUser->email = $email;

            $user = $this->socialAuthService->handleSocialLogin('github', $githubUser);

            // Redirect to intended URL or profile
            $redirectUrl = session()->pull('url.intended', route('profile.show'));

            return redirect($redirectUrl)->with('success', 
                'Successfully logged in with GitHub!');

        } catch (\Exception $e) {
            Log::error("GitHub login failed", [
                'error' => $e->getMessage(),
                'provider' => 'github',
            ]);

            return redirect()->route('login')
                ->with('error', 'Failed to login with GitHub. Please try again.');
        }
    }

    /**
     * Link GitHub account to existing user.
     */
    public function link()
    {
        if (!$this->socialAuthService->isProviderConfigured('github')) {
            return redirect()->route('profile.edit')
                ->with('error', 'GitHub authentication is not configured.');
        }

        session(['linking_github' => true]);

        return Socialite::driver('github')
            ->scopes(['user:email'])
            ->redirect();
    }

    /**
     * Handle GitHub linking callback.
     */
    public function linkCallback()
    {
        try {
            // Verify this is a linking session
            if (!session('linking_github')) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Invalid linking session. Please try again.');
            }

            $githubUser = Socialite::driver('github')->user();
            $user = Auth::user();

            // GitHub might not provide email publicly, so we need to handle this
            $email = $githubUser->getEmail();
            
            if (!$email) {
                // Try to get primary email from GitHub API
                try {
                    $emails = Socialite::driver('github')->getHttpClient()->get('https://api.github.com/user/emails', [
                        'headers' => [
                            'Authorization' => 'token ' . $githubUser->token,
                            'Accept' => 'application/vnd.github.v3+json',
                        ],
                    ]);
                    
                    $emailData = json_decode($emails->getBody(), true);
                    $primaryEmail = collect($emailData)->firstWhere('primary', true);
                    $email = $primaryEmail['email'] ?? null;
                } catch (\Exception $emailException) {
                    Log::warning("Could not fetch GitHub primary email for linking", [
                        'github_id' => $githubUser->getId(),
                        'error' => $emailException->getMessage(),
                    ]);
                }
            }

            if (!$email) {
                session()->forget('linking_github');
                return redirect()->route('profile.edit')
                    ->with('error', 'GitHub account must have a public email address to link. Please check your GitHub privacy settings.');
            }

            // Update the user object with the fetched email
            $githubUser->email = $email;

            // Check if this GitHub account is already linked to the current user
            if ($user->hasSocialAccount('github')) {
                session()->forget('linking_github');
                return redirect()->route('profile.edit')
                    ->with('error', 'This GitHub account is already linked to your profile.');
            }

            // Check if GitHub account is linked to another user
            $existingUser = User::findBySocialAccount('github', $githubUser->getId());
            if ($existingUser && $existingUser->id !== $user->id) {
                Log::warning("GitHub account linking attempt to already linked account", [
                    'provider' => 'github',
                    'github_id' => $githubUser->getId(),
                    'attempting_user_id' => $user->id,
                    'existing_user_id' => $existingUser->id,
                ]);
                
                session()->forget('linking_github');
                return redirect()->route('profile.edit')
                    ->with('error', 'This GitHub account is already linked to another user.');
            }

            // Link the account
            $this->socialAuthService->linkSocialAccount($user, 'github', $githubUser);

            // Clear linking session
            session()->forget('linking_github');

            return redirect()->route('profile.edit')
                ->with('success', 'Successfully linked GitHub account!');

        } catch (\Exception $e) {
            Log::error("GitHub account linking failed", [
                'error' => $e->getMessage(),
                'provider' => 'github',
                'user_id' => Auth::id(),
            ]);

            // Clear linking session on error
            session()->forget('linking_github');

            return redirect()->route('profile.edit')
                ->with('error', 'Failed to link GitHub account. Please try again.');
        }
    }

    /**
     * Disconnect GitHub account.
     */
    public function disconnect(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasSocialAccount('github')) {
                return redirect()->back()
                    ->with('error', 'No GitHub account connected to your profile.');
            }

            // Additional security: Ensure the GitHub account belongs to the authenticated user
            $socialAccount = $user->socialAccounts()->byProvider('github')->first();
            if (!$socialAccount || $socialAccount->user_id !== $user->id) {
                return redirect()->back()
                    ->with('error', 'Unauthorized action. You can only disconnect your own GitHub account.');
            }

            $this->socialAuthService->disconnectSocialAccount($user, 'github');

            return redirect()->back()
                ->with('success', 'Successfully disconnected GitHub account.');

        } catch (\Exception $e) {
            Log::error("GitHub account disconnect failed", [
                'error' => $e->getMessage(),
                'provider' => 'github',
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to disconnect GitHub account. Please try again.');
        }
    }
}
