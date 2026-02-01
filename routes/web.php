<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\RegisterController;


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    // Registration
    Route::controller(RegisterController::class)->group(function () {
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register');
    });

    // Login
    Route::controller(LoginController::class)->group(function () {
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login');
    });

    // Password Reset
    Route::controller(ForgotPasswordController::class)->group(function () {
        Route::get('forgot-password', 'showLinkRequestForm')->name('password.request');
        Route::post('forgot-password', 'sendResetLinkEmail')->name('password.email');
    });
    
    Route::controller(ResetPasswordController::class)->group(function () {
        Route::get('reset-password/{token}', 'showResetForm')->name('password.reset');
        Route::post('reset-password', 'reset')->name('password.update');
    });
});

// Logout (Authenticated only)
Route::middleware('auth')->post('logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Email Verification Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', fn() => view('auth.verify-email'))->name('verification.notice');
    
    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        
        // Send welcome email after successful verification
        $emailService = new \App\Services\EmailService();
        $emailService->sendWelcomeEmail($request->user());
        
        return redirect('/')->with('verified', true)->with('status', 'Welcome! Your email has been verified successfully.');
    })->middleware('signed')->name('verification.verify');
    
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Profile Management
    Route::get('/profile', function () {
        return view('profile.show');
    })->name('profile.show');
    
    Route::get('/profile/edit', function () {
        return view('profile.edit');
    })->name('profile.edit');
    
    // Profile API Routes (for AJAX calls)
    Route::prefix('api/profile')->group(function () {
        Route::get('/states/{country}', function ($country) {
            return \App\Models\State::where('country_id', $country)
                ->orderBy('name')
                ->get(['id', 'name']);
        })->name('api.profile.states');
        
        Route::get('/cities/{state}', function ($state) {
            return \App\Models\City::where('state_id', $state)
                ->orderBy('name')
                ->get(['id', 'name']);
        })->name('api.profile.cities');
    });
    
    // Public Profile Viewing
    Route::get('/users/{user}', function (\App\Models\User $user) {
        // Check if current user can view the target profile
        if (!auth()->user()->canViewProfile($user)) {
            abort(403, 'This profile is private');
        }
        
        return view('profile.public', ['user' => $user]);
    })->name('profile.public');
});

// Social Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/auth/{provider}', [App\Http\Controllers\SocialAuthController::class, 'redirect'])
        ->name('social.redirect');
    
    Route::get('/auth/{provider}/callback', [App\Http\Controllers\SocialAuthController::class, 'callback'])
        ->name('social.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/auth/{provider}/disconnect', [App\Http\Controllers\SocialAuthController::class, 'disconnect'])
        ->name('social.disconnect');
    
    Route::get('/auth/{provider}/link', [App\Http\Controllers\SocialAuthController::class, 'link'])
        ->name('social.link');
    
    Route::get('/auth/{provider}/link/callback', [App\Http\Controllers\SocialAuthController::class, 'linkCallback'])
        ->name('social.link.callback');
});

Route::get('/api/social/providers', [App\Http\Controllers\SocialAuthController::class, 'providers'])
    ->name('social.providers');
