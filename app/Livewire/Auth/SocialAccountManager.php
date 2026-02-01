<?php

namespace App\Livewire\Auth;

use App\Services\SocialAuthService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SocialAccountManager extends Component
{
    public $user;
    public $providers;

    protected $socialAuthService;

    public function boot(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    public function mount()
    {
        $this->user = Auth::user();
        $this->providers = $this->socialAuthService->getAvailableProviders();
    }

    public function disconnect($provider)
    {
        try {
            $this->socialAuthService->disconnectSocialAccount($this->user, $provider);
            
            $this->dispatch('notification', [
                'type' => 'success',
                'message' => "Successfully disconnected {$this->providers[$provider]['name']} account."
            ]);
            
            // Refresh the component
            $this->user->refresh();
            
        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.auth.social-account-manager');
    }
}
