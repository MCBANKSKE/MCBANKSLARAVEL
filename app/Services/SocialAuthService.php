<?php

namespace App\Services;

use App\Models\User;
use App\Models\SocialAccount;
use Laravel\Socialite\Contracts\User as SocialUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SocialAuthService
{
    /**
     * Handle social login/registration.
     */
    public function handleSocialLogin(string $provider, SocialUser $socialUser): User
    {
        // Find existing social account
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            // User already has this social account
            $user = $socialAccount->user;
            
            // Update social account data
            $this->updateSocialAccount($socialAccount, $socialUser);
            
            // Log in the user
            Auth::login($user, true);
            
            return $user;
        }

        // Check if user exists with same email
        if ($socialUser->getEmail()) {
            $existingUser = User::where('email', $socialUser->getEmail())->first();
            
            if ($existingUser) {
                // Link social account to existing user
                $this->linkSocialAccount($existingUser, $provider, $socialUser);
                
                // Log in the user
                Auth::login($existingUser, true);
                
                return $existingUser;
            }
        }

        // Create new user from social account
        $user = $this->createUserFromSocial($provider, $socialUser);
        
        // Log in the new user
        Auth::login($user, true);
        
        return $user;
    }

    /**
     * Update existing social account with fresh data.
     */
    private function updateSocialAccount(SocialAccount $socialAccount, SocialUser $socialUser): void
    {
        $socialAccount->update([
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken,
            'provider_expires_in' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn)->timestamp : null,
            'nickname' => $socialUser->getNickname(),
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
        ]);
    }

    /**
     * Link social account to existing user.
     */
    private function linkSocialAccount(User $user, string $provider, SocialUser $socialUser): SocialAccount
    {
        return $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken,
            'provider_expires_in' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn)->timestamp : null,
            'provider_data' => $socialUser->user,
            'nickname' => $socialUser->getNickname(),
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
        ]);
    }

    /**
     * Create new user from social account.
     */
    private function createUserFromSocial(string $provider, SocialUser $socialUser): User
    {
        // Generate a unique username if needed
        $username = $this->generateUniqueUsername($socialUser->getNickname() ?: $socialUser->getName());
        
        $userData = [
            'name' => $socialUser->getName() ?: $username,
            'email' => $socialUser->getEmail(),
            'password' => bcrypt(\Str::random(32)),
            'email_verified_at' => $socialUser->getEmail() ? now() : null,
        ];

        $user = User::create($userData);

        // Create social account
        $this->linkSocialAccount($user, $provider, $socialUser);

        // Create user profile
        $profile = $user->getOrCreateProfile();
        
        // Update profile with social data
        if ($socialUser->getName()) {
            $profile->bio = "Joined via " . ucfirst($provider);
            $profile->updateCompletionPercentage();
        }

        // Assign default role
        $user->assignRole('customer');

        Log::info("New user created via {$provider}", [
            'user_id' => $user->id,
            'email' => $user->email,
            'provider' => $provider,
        ]);

        return $user;
    }

    /**
     * Generate unique username.
     */
    private function generateUniqueUsername(?string $baseName): string
    {
        if (!$baseName) {
            return 'user_' . strtolower(\Str::random(8));
        }

        $username = strtolower(str_replace(' ', '', $baseName));
        $originalUsername = $username;
        $counter = 1;

        while (User::where('name', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Disconnect social account.
     */
    public function disconnectSocialAccount(User $user, string $provider): bool
    {
        $socialAccount = $user->socialAccounts()->byProvider($provider)->first();
        
        if (!$socialAccount) {
            return false;
        }

        // Don't allow disconnecting if user has no password and no other social accounts
        if (!$user->password && $user->socialAccounts()->count() <= 1) {
            throw new \Exception('Cannot disconnect the only authentication method. Please set a password first.');
        }

        $socialAccount->delete();
        
        Log::info("Social account disconnected", [
            'user_id' => $user->id,
            'provider' => $provider,
        ]);

        return true;
    }

    /**
     * Get available social providers.
     */
    public function getAvailableProviders(): array
    {
        return [
            'google' => [
                'name' => 'Google',
                'icon' => 'fab fa-google',
                'color' => 'bg-red-500 hover:bg-red-600',
            ],
            'github' => [
                'name' => 'GitHub',
                'icon' => 'fab fa-github',
                'color' => 'bg-gray-800 hover:bg-gray-900',
            ],
            'twitter' => [
                'name' => 'Twitter',
                'icon' => 'fab fa-twitter',
                'color' => 'bg-blue-400 hover:bg-blue-500',
            ],
        ];
    }

    /**
     * Check if provider is configured.
     */
    public function isProviderConfigured(string $provider): bool
    {
        $configKey = "services.{$provider}";
        
        return config("{$configKey}.client_id") && 
               config("{$configKey}.client_secret") && 
               config("{$configKey}.redirect");
    }
}
