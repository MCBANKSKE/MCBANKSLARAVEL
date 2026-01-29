<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

/**
 * Login Form Component
 *
 * INSTRUCTIONS FOR DEVELOPERS:
 * ----------------------------
 * 1. Include in Blade:
 *      <livewire:auth.login-form />
 *
 * 2. Routes:
 *      - Ensure the following routes exist:
 *          /admin   -> admin panel
 *          /member  -> member panel
 *          /        -> fallback
 *          verification.notice -> email verification page
 *
 * 3. Role-based redirection:
 *      - Admin: /admin
 *      - Member: /member (requires verified email)
 *      - Others: /
 *
 * 4. Authentication:
 *      - Uses Laravel Auth::attempt()
 *      - Remember me is supported via $remember property
 */
class LoginForm extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $user = Auth::user();
            return redirect()->intended($this->getDashboardRoute($user));
        }

        $this->addError('email', 'These credentials do not match our records.');
    }

    /**
     * Determine dashboard route based on role
     */
    protected function getDashboardRoute($user)
    {
        if ($user->hasRole('admin')) {
            return '/admin';
        }

        if ($user->hasRole('member')) {
            if (is_null($user->email_verified_at)) {
                return route('verification.notice');
            }
            return '/member';
        }

        return '/'; // fallback for unknown roles
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
