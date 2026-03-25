<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuthentication extends Component
{
    public bool $enabled = false;
    public string $code = '';
    public array $recoveryCodes = [];
    public bool $showQrCode = false;
    public string $qrCode = '';
    public string $secret = '';
    public bool $showRecoveryCodes = false;
    public bool $confirmingDisable = false;
    public string $disablePassword = '';

    protected TwoFactorService $twoFactorService;

    protected function rules(): array
    {
        return [
            'code' => 'required|string|digits:6',
            'disablePassword' => 'required|string|current_password',
        ];
    }

    public function boot(TwoFactorService $twoFactorService): void
    {
        $this->twoFactorService = $twoFactorService;
    }

    public function mount(): void
    {
        $this->loadTwoFactorStatus();
    }

    public function loadTwoFactorStatus(): void
    {
        $user = Auth::user();
        $status = $this->twoFactorService->getStatus($user);
        
        $this->enabled = $status['enabled'];
        $this->recoveryCodes = $user->twoFactorAuthentication?->recovery_codes ?? [];
    }

    public function enableTwoFactor(): void
    {
        $this->validate(['code' => 'required|string|digits:6']);

        $user = Auth::user();
        
        $validation = $this->twoFactorService->validateSetup($user, $this->code);
        
        if (!$validation['valid']) {
            $this->addError('code', $validation['message']);
            return;
        }

        if ($this->twoFactorService->enable($user, $this->code)) {
            $this->enabled = true;
            $this->showQrCode = false;
            $this->showRecoveryCodes = false;
            $this->code = '';
            $this->secret = '';
            $this->loadTwoFactorStatus();
            
            $this->dispatch('two-factor-enabled');
            session()->flash('success', 'Two-factor authentication has been enabled successfully.');
        } else {
            $this->addError('code', 'Failed to enable two-factor authentication. Please try again.');
        }
    }

    public function showSetupForm(): void
    {
        $user = Auth::user();
        $this->secret = $this->twoFactorService->generateSecretKey($user);
        $this->qrCode = $this->twoFactorService->generateQrCode($user, $this->secret);
        $this->showQrCode = true;
        $this->showRecoveryCodes = false;
        $this->code = '';
    }

    public function hideSetupForm(): void
    {
        $this->showQrCode = false;
        $this->twoFactorService->clearSecret(Auth::user());
        $this->code = '';
        $this->secret = '';
        $this->qrCode = '';
    }

    public function disableTwoFactor(): void
    {
        $this->validate(['disablePassword' => 'required|string|current_password']);

        $user = Auth::user();
        $this->twoFactorService->disable($user);
        
        $this->enabled = false;
        $this->confirmingDisable = false;
        $this->disablePassword = '';
        $this->loadTwoFactorStatus();
        
        $this->dispatch('two-factor-disabled');
        session()->flash('success', 'Two-factor authentication has been disabled.');
    }

    public function confirmDisable(): void
    {
        $this->confirmingDisable = true;
    }

    public function cancelDisable(): void
    {
        $this->confirmingDisable = false;
        $this->disablePassword = '';
    }

    public function regenerateRecoveryCodes(): void
    {
        $user = Auth::user();
        $this->recoveryCodes = $this->twoFactorService->regenerateRecoveryCodes($user);
        $this->showRecoveryCodes = true;
        
        session()->flash('success', 'New recovery codes have been generated.');
    }

    public function toggleRecoveryCodes(): void
    {
        $this->showRecoveryCodes = !$this->showRecoveryCodes;
    }

    public function downloadRecoveryCodes()
    {
        $user = Auth::user();
        $codes = $user->twoFactorAuthentication->recovery_codes ?? [];
        
        $content = "Two-Factor Authentication Recovery Codes\n";
        $content .= "User: {$user->email}\n";
        $content .= "Generated: " . now()->toDateTimeString() . "\n";
        $content .= "========================================\n\n";
        
        foreach ($codes as $index => $code) {
            $content .= ($index + 1) . ". {$code}\n";
        }
        
        $content .= "\n========================================\n";
        $content .= "Keep these codes safe and secure!\n";
        $content .= "Each code can only be used once.\n";

        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            '2fa-recovery-codes-' . date('Y-m-d') . '.txt'
        );
    }

    public function getHasRemainingRecoveryCodesProperty(): bool
    {
        $user = Auth::user();
        return $user->twoFactorAuthentication?->hasRecoveryCodes() ?? false;
    }

    public function getRemainingRecoveryCodesCountProperty(): int
    {
        $user = Auth::user();
        return $user->twoFactorAuthentication?->getRemainingRecoveryCodesCount() ?? 0;
    }

    public function getLastUsedAtProperty(): ?string
    {
        $user = Auth::user();
        return $user->twoFactorAuthentication?->last_used_at?->diffForHumans();
    }

    public function render()
    {
        return view('livewire.profile.two-factor-authentication');
    }
}
