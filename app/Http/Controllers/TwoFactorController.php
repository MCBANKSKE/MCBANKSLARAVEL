<?php

namespace App\Http\Controllers;

use App\Http\Requests\TwoFactorChallengeRequest;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TwoFactorController extends Controller
{
    protected TwoFactorService $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
        $this->middleware('auth');
    }

    /**
     * Show the two-factor challenge page.
     */
    public function showChallenge()
    {
        // If user doesn't have 2FA enabled, redirect to dashboard
        if (!Auth::user()->hasTwoFactorEnabled()) {
            return redirect()->intended();
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the two-factor challenge.
     */
    public function verify(TwoFactorChallengeRequest $request)
    {
        $user = Auth::user();
        $code = $request->input('code');

        // Check rate limiting
        if (!$this->twoFactorService->checkRateLimit($user)) {
            return back()
                ->withErrors(['code' => 'Too many attempts. Please try again later.'])
                ->withInput();
        }

        if ($this->twoFactorService->verify($user, $code)) {
            // Mark 2FA as verified for this session
            Session::put('2fa.verified', true);
            Session::put('2fa.verified_at', now());

            // Update last used timestamp
            $user->twoFactorAuthentication->updateLastUsed();

            // Clear rate limit
            $this->twoFactorService->clearRateLimit($user);

            // Redirect to intended URL or dashboard
            return redirect()->intended();
        }

        return back()
            ->withErrors(['code' => 'The authentication code is invalid.'])
            ->withInput();
    }

    /**
     * Show the recovery code form.
     */
    public function showRecoveryForm()
    {
        if (!Auth::user()->hasTwoFactorEnabled()) {
            return redirect()->intended();
        }

        return view('auth.two-factor-recovery');
    }

    /**
     * Verify using recovery code.
     */
    public function verifyRecovery(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10',
        ]);

        $user = Auth::user();
        $code = strtoupper(str_replace('-', '', $request->input('code')));

        // Check rate limiting
        if (!$this->twoFactorService->checkRateLimit($user)) {
            return back()
                ->withErrors(['code' => 'Too many attempts. Please try again later.'])
                ->withInput();
        }

        if ($this->twoFactorService->verifyBackupCode($user, $code)) {
            // Mark 2FA as verified for this session
            Session::put('2fa.verified', true);
            Session::put('2fa.verified_at', now());
            Session::put('2fa.used_recovery_code', true);

            // Clear rate limit
            $this->twoFactorService->clearRateLimit($user);

            // Send notification about recovery code usage
            $this->twoFactorService->sendNotification($user, 'recovery_codes_used');

            return redirect()->intended()->with('warning', 
                'A recovery code was used to access your account. Consider regenerating your recovery codes for security.'
            );
        }

        return back()
            ->withErrors(['code' => 'The recovery code is invalid.'])
            ->withInput();
    }

    /**
     * Logout user from 2FA challenge.
     */
    public function logout()
    {
        Auth::logout();
        Session::forget(['2fa.verified', '2fa.verified_at', '2fa.intended', '2fa.used_recovery_code']);

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }
}
