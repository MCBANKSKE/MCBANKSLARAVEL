<?php

namespace App\Livewire\Auth;

use App\Services\SocialAuthService;
use Livewire\Component;

class SocialLogin extends Component
{
    public $showLabel = true;
    public $showDivider = false;
    public $showCompact = false;
    public $showIconsOnly = false;
    public $redirectUrl = null;

    protected $socialAuthService;

    public function boot(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    public function mount($showLabel = true, $showDivider = false, $showCompact = false, $showIconsOnly = false, $redirectUrl = null)
    {
        $this->showLabel = $showLabel;
        $this->showDivider = $showDivider;
        $this->showCompact = $showCompact;
        $this->showIconsOnly = $showIconsOnly;
        $this->redirectUrl = $redirectUrl;
    }

    public function getProvidersProperty()
    {
        $providers = $this->socialAuthService->getAvailableProviders();
        
        foreach ($providers as $key => $provider) {
            $providers[$key]['configured'] = $this->socialAuthService->isProviderConfigured($key);
        }

        return $providers;
    }

    public function redirectToProvider($provider)
    {
        $url = route('social.redirect', $provider);
        
        if ($this->redirectUrl) {
            $url .= '?redirect=' . urlencode($this->redirectUrl);
        }
        
        return redirect($url);
    }

    public function render()
    {
        return view('livewire.auth.social-login');
    }
}
