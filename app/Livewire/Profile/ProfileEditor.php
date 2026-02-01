<?php

namespace App\Livewire\Profile;

use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Profile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class ProfileEditor extends Component
{
    use WithFileUploads;

    public Profile $profile;

    #[Validate('nullable|string|max:500')]
    public $bio;

    #[Validate('nullable|string|max:20')]
    public $phone;

    #[Validate('nullable|url|max:255')]
    public $website;

    #[Validate('nullable|integer|exists:countries,id')]
    public $country_id;

    #[Validate('nullable|integer|exists:states,id')]
    public $state_id;

    #[Validate('nullable|integer|exists:cities,id')]
    public $city_id;

    #[Validate('nullable|string|max:255')]
    public $address;

    #[Validate('nullable|image|max:5120')] // 5MB max
    public $avatar;

    // Privacy settings
    public $show_email = false;
    public $show_phone = true;
    public $show_location = true;
    public $show_website = true;
    public $allow_messages = true;
    public $profile_public = true;

    // UI state
    public $countries = [];
    public $states = [];
    public $cities = [];
    public $isLoading = false;

    public function mount()
    {
        $user = Auth::user();
        $this->profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        
        $this->loadProfileData();
        $this->loadGeographicalData();
    }

    protected function loadProfileData()
    {
        $this->bio = $this->profile->bio;
        $this->phone = $this->profile->phone;
        $this->website = $this->profile->website;
        $this->country_id = $this->profile->country_id;
        $this->state_id = $this->profile->state_id;
        $this->city_id = $this->profile->city_id;
        $this->address = $this->profile->address;

        // Load privacy settings
        $privacy = $this->profile->privacy_settings ?? $this->profile->getDefaultPrivacySettings();
        $this->show_email = $privacy['show_email'] ?? false;
        $this->show_phone = $privacy['show_phone'] ?? true;
        $this->show_location = $privacy['show_location'] ?? true;
        $this->show_website = $privacy['show_website'] ?? true;
        $this->allow_messages = $privacy['allow_messages'] ?? true;
        $this->profile_public = $privacy['profile_public'] ?? true;
    }

    protected function loadGeographicalData()
    {
        $this->countries = Country::orderBy('name')->get();
        
        if ($this->country_id) {
            $this->states = State::where('country_id', $this->country_id)->orderBy('name')->get();
        }
        
        if ($this->state_id) {
            $this->cities = City::where('state_id', $this->state_id)->orderBy('name')->get();
        }
    }

    public function updatedCountryId()
    {
        $this->reset(['state_id', 'city_id']);
        $this->states = $this->country_id 
            ? State::where('country_id', $this->country_id)->orderBy('name')->get()
            : [];
        $this->cities = [];
    }

    public function updatedStateId()
    {
        $this->reset('city_id');
        $this->cities = $this->state_id
            ? City::where('state_id', $this->state_id)->orderBy('name')->get()
            : [];
    }

    public function save()
    {
        $this->validate();

        $this->isLoading = true;

        try {
            // Handle avatar upload
            if ($this->avatar) {
                $avatarService = app(\App\Services\AvatarUploadService::class);
                $avatarService->uploadAvatar($this->avatar, $this->profile);
            }

            // Update profile data
            $this->profile->update([
                'bio' => $this->bio,
                'phone' => $this->phone,
                'website' => $this->website,
                'country_id' => $this->country_id,
                'state_id' => $this->state_id,
                'city_id' => $this->city_id,
                'address' => $this->address,
                'privacy_settings' => [
                    'show_email' => $this->show_email,
                    'show_phone' => $this->show_phone,
                    'show_location' => $this->show_location,
                    'show_website' => $this->show_website,
                    'allow_messages' => $this->allow_messages,
                    'profile_public' => $this->profile_public,
                ],
            ]);

            // Update completion percentage
            $this->profile->updateCompletionPercentage();

            $this->dispatch('profile-updated');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Profile updated successfully!'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error updating profile: ' . $e->getMessage()
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function getCompletionPercentageProperty()
    {
        return $this->profile->completion_percentage ?? 0;
    }

    public function getCompletionColorProperty()
    {
        $percentage = $this->completion_percentage;
        
        if ($percentage < 25) {
            return 'bg-red-500';
        } elseif ($percentage < 50) {
            return 'bg-yellow-500';
        } elseif ($percentage < 75) {
            return 'bg-blue-500';
        } else {
            return 'bg-green-500';
        }
    }

    public function getCompletionMessageProperty()
    {
        $percentage = $this->completion_percentage;
        
        if ($percentage == 0) {
            return 'Start building your profile';
        } elseif ($percentage < 25) {
            return 'Profile just getting started';
        } elseif ($percentage < 50) {
            return 'Profile partially complete';
        } elseif ($percentage < 75) {
            return 'Profile almost complete';
        } elseif ($percentage < 100) {
            return 'Profile nearly complete';
        } else {
            return 'Profile complete!';
        }
    }

    public function render()
    {
        return view('livewire.profile.profile-editor')
            ->layout('layouts.app');
    }
}
