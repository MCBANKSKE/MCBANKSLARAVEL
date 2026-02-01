<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SocialAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SocialAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and add sample social accounts
        User::all()->each(function ($user) {
            // Randomly assign social accounts to users (50% chance)
            if (rand(1, 2) === 1) {
                $this->createSampleSocialAccount($user, 'google');
            }
            
            if (rand(1, 3) === 1) { // 33% chance for GitHub
                $this->createSampleSocialAccount($user, 'github');
            }
            
            if (rand(1, 4) === 1) { // 25% chance for Twitter
                $this->createSampleSocialAccount($user, 'twitter');
            }
        });

        $this->command->info('Sample social accounts created for users successfully!');
    }

    /**
     * Create a sample social account for a user.
     */
    private function createSampleSocialAccount(User $user, string $provider): void
    {
        // Skip if user already has this provider
        if ($user->hasSocialAccount($provider)) {
            return;
        }

        $sampleData = $this->getSampleSocialData($provider, $user);
        
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $sampleData['provider_id'],
            'provider_token' => 'sample_token_' . \Str::random(32),
            'provider_refresh_token' => 'sample_refresh_token_' . \Str::random(32),
            'provider_expires_in' => now()->addHours(2)->timestamp,
            'provider_data' => $sampleData['raw_data'],
            'nickname' => $sampleData['nickname'],
            'name' => $sampleData['name'],
            'email' => $sampleData['email'],
            'avatar' => $sampleData['avatar'],
        ]);

        Log::info("Created sample {$provider} social account for user {$user->id}");
    }

    /**
     * Get sample social data for different providers.
     */
    private function getSampleSocialData(string $provider, User $user): array
    {
        $baseData = [
            'email' => $user->email,
            'name' => $user->name,
        ];

        return match ($provider) {
            'google' => [
                'provider_id' => 'google_' . $user->id . '_' . \Str::random(10),
                'nickname' => strtolower(str_replace(' ', '', $user->name)),
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => 'https://lh3.googleusercontent.com/a/default-user=' . \Str::random(10),
                'raw_data' => [
                    'id' => 'google_' . $user->id,
                    'verified_email' => true,
                    'picture' => 'https://lh3.googleusercontent.com/a/default-user=' . \Str::random(10),
                    'locale' => 'en',
                ],
            ],
            
            'github' => [
                'provider_id' => $user->id * 1000 + rand(1, 999),
                'nickname' => strtolower(str_replace(' ', '', $user->name)) . 'gh',
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => 'https://avatars.githubusercontent.com/u/' . ($user->id * 1000) . '?v=4',
                'raw_data' => [
                    'login' => strtolower(str_replace(' ', '', $user->name)) . 'gh',
                    'id' => $user->id * 1000 + rand(1, 999),
                    'node_id' => 'MDQ6VXNlcj' . ($user->id * 1000),
                    'avatar_url' => 'https://avatars.githubusercontent.com/u/' . ($user->id * 1000) . '?v=4',
                    'gravatar_id' => '',
                    'url' => 'https://api.github.com/users/' . strtolower(str_replace(' ', '', $user->name)) . 'gh',
                    'html_url' => 'https://github.com/' . strtolower(str_replace(' ', '', $user->name)) . 'gh',
                    'type' => 'User',
                ],
            ],
            
            'twitter' => [
                'provider_id' => $user->id * 5000 + rand(1, 999),
                'nickname' => '@' . strtolower(str_replace(' ', '', $user->name)) . 'tw',
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => 'https://pbs.twimg.com/profile_images/' . \Str::random(20) . '/default.jpg',
                'raw_data' => [
                    'id_str' => (string) ($user->id * 5000 + rand(1, 999)),
                    'name' => $user->name,
                    'screen_name' => strtolower(str_replace(' ', '', $user->name)) . 'tw',
                    'location' => 'Nairobi, Kenya',
                    'description' => 'Software Developer | Tech Enthusiast',
                    'followers_count' => rand(100, 5000),
                    'friends_count' => rand(50, 1000),
                    'listed_count' => rand(5, 50),
                    'favourites_count' => rand(100, 2000),
                    'statuses_count' => rand(500, 10000),
                    'profile_image_url' => 'http://pbs.twimg.com/profile_images/' . \Str::random(20) . '/default.jpg',
                    'profile_image_url_https' => 'https://pbs.twimg.com/profile_images/' . \Str::random(20) . '/default.jpg',
                    'verified' => false,
                ],
            ],
            
            default => $baseData,
        };
    }
}
