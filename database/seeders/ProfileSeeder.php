<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Avatar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure storage directories exist
        Storage::disk('public')->makeDirectory('avatars');
        Storage::disk('public')->makeDirectory('avatars/thumbnails');

        // Get all users and create profiles for them
        User::all()->each(function ($user) {
            // Skip if user already has a profile
            if ($user->profile) {
                return;
            }

            // Create profile with sample data
            $profile = Profile::create([
                'user_id' => $user->id,
                'bio' => 'Passionate developer and tech enthusiast. Love building amazing applications and exploring new technologies.',
                'phone' => '+1 (555) ' . rand(100, 999) . '-' . rand(1000, 9999),
                'website' => 'https://example.com',
                'country_id' => 236, // Kenya (assuming it exists in countries table)
                'state_id' => null, // Will be set based on actual data
                'city_id' => null,  // Will be set based on actual data
                'address' => rand(100, 999) . ' Main St, Apt ' . rand(1, 999),
                'privacy_settings' => [
                    'show_email' => false,
                    'show_phone' => true,
                    'show_location' => true,
                    'show_website' => true,
                    'allow_messages' => true,
                    'profile_public' => true,
                ],
            ]);

            // Calculate completion percentage
            $profile->updateCompletionPercentage();

            // Create a sample avatar (in production, you'd use actual images)
            // For now, we'll just create the record without actual files
            if ($user->id % 3 === 0) { // Give avatar to every 3rd user
                Avatar::create([
                    'profile_id' => $profile->id,
                    'original_name' => 'sample-avatar.jpg',
                    'file_path' => 'avatars/sample-avatar-' . $user->id . '.jpg',
                    'file_name' => 'sample-avatar-' . $user->id . '.jpg',
                    'mime_type' => 'image/jpeg',
                    'file_size' => 12345,
                    'width' => 300,
                    'height' => 300,
                    'disk' => 'public',
                ]);
            }
        });

        $this->command->info('Profiles created for all users successfully!');
    }
}
