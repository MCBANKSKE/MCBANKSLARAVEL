<?php

namespace App\Livewire\Profile;

use App\Models\Avatar;
use App\Models\Profile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class AvatarUpload extends Component
{
    use WithFileUploads;

    public Profile $profile;
    
    #[Validate('nullable|image|max:5120')] // 5MB max
    public $avatar;

    public $isUploading = false;
    public $uploadProgress = 0;
    public $previewUrl = null;

    public function mount()
    {
        $user = Auth::user();
        $this->profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $this->previewUrl = $this->profile->avatar?->thumbnail_url;
    }

    public function updatedAvatar()
    {
        $this->validateOnly('avatar');
        
        // Create preview
        $this->previewUrl = $this->avatar->temporaryUrl();
    }

    public function uploadAvatar()
    {
        $this->validate();

        if (!$this->avatar) {
            return;
        }

        $this->isUploading = true;
        $this->uploadProgress = 0;

        try {
            // Simulate upload progress
            $this->dispatch('upload-progress', progress: 25);
            
            $avatarService = app(\App\Services\AvatarUploadService::class);
            
            $this->dispatch('upload-progress', progress: 50);
            
            $avatar = $avatarService->uploadAvatar($this->avatar, $this->profile);
            
            $this->dispatch('upload-progress', progress: 75);
            
            // Update preview URL
            $this->previewUrl = $avatar->thumbnail_url;
            
            $this->dispatch('upload-progress', progress: 100);
            
            // Reset avatar input
            $this->reset('avatar');
            
            // Notify parent component
            $this->dispatch('avatar-updated', avatar: $avatar);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Avatar uploaded successfully!'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error uploading avatar: ' . $e->getMessage()
            ]);
        } finally {
            $this->isUploading = false;
            $this->uploadProgress = 0;
        }
    }

    public function removeAvatar()
    {
        if (!$this->profile->avatar) {
            return;
        }

        try {
            $this->profile->avatar->delete();
            $this->previewUrl = null;
            
            $this->dispatch('avatar-removed');
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Avatar removed successfully!'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error removing avatar: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelUpload()
    {
        $this->reset('avatar', 'previewUrl', 'isUploading', 'uploadProgress');
        
        // Reset to current avatar
        $this->previewUrl = $this->profile->avatar?->thumbnail_url;
    }

    public function getHasAvatarProperty()
    {
        return $this->profile->avatar !== null;
    }

    public function getCurrentAvatarUrlProperty()
    {
        return $this->profile->avatar?->url;
    }

    public function getCurrentThumbnailUrlProperty()
    {
        return $this->profile->avatar?->thumbnail_url;
    }

    public function getSupportedFormatsProperty()
    {
        return implode(', ', Avatar::getAllowedExtensions());
    }

    public function getMaxFileSizeProperty()
    {
        $bytes = Avatar::getMaxFileSize();
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function render()
    {
        return view('livewire.profile.avatar-upload');
    }
}
