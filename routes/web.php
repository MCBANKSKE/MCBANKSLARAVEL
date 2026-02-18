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
    // Google
    Route::get('/auth/google/redirect', [App\Http\Controllers\Social\GoogleController::class, 'redirect'])
        ->name('social.google.redirect');
    Route::get('/auth/google/callback', [App\Http\Controllers\Social\GoogleController::class, 'callback'])
        ->name('social.google.callback');
    
    // GitHub
    Route::get('/auth/github/redirect', [App\Http\Controllers\Social\GitHubController::class, 'redirect'])
        ->name('social.github.redirect');
    Route::get('/auth/github/callback', [App\Http\Controllers\Social\GitHubController::class, 'callback'])
        ->name('social.github.callback');
    
    // Facebook
    Route::get('/auth/facebook/redirect', [App\Http\Controllers\Social\FacebookController::class, 'redirect'])
        ->name('social.facebook.redirect');
    Route::get('/auth/facebook/callback', [App\Http\Controllers\Social\FacebookController::class, 'callback'])
        ->name('social.facebook.callback');
});

Route::middleware('auth')->group(function () {
    // Google Account Management
    Route::post('/auth/google/disconnect', [App\Http\Controllers\Social\GoogleController::class, 'disconnect'])
        ->name('social.google.disconnect');
    Route::get('/auth/google/link', [App\Http\Controllers\Social\GoogleController::class, 'link'])
        ->name('social.google.link');
    Route::get('/auth/google/link/callback', [App\Http\Controllers\Social\GoogleController::class, 'linkCallback'])
        ->name('social.google.link.callback');
    
    // GitHub Account Management
    Route::post('/auth/github/disconnect', [App\Http\Controllers\Social\GitHubController::class, 'disconnect'])
        ->name('social.github.disconnect');
    Route::get('/auth/github/link', [App\Http\Controllers\Social\GitHubController::class, 'link'])
        ->name('social.github.link');
    Route::get('/auth/github/link/callback', [App\Http\Controllers\Social\GitHubController::class, 'linkCallback'])
        ->name('social.github.link.callback');
    
    // Facebook Account Management
    Route::post('/auth/facebook/disconnect', [App\Http\Controllers\Social\FacebookController::class, 'disconnect'])
        ->name('social.facebook.disconnect');
    Route::get('/auth/facebook/link', [App\Http\Controllers\Social\FacebookController::class, 'link'])
        ->name('social.facebook.link');
    Route::get('/auth/facebook/link/callback', [App\Http\Controllers\Social\FacebookController::class, 'linkCallback'])
        ->name('social.facebook.link.callback');
});

// Legacy routes for backward compatibility (redirect to new routes)
Route::get('/auth/{provider}', function ($provider) {
    $routeName = "social.{$provider}.redirect";
    if (Route::has($routeName)) {
        return redirect()->route($routeName);
    }
    return redirect()->route('login')->with('error', 'Social provider not supported.');
})->name('social.redirect');

Route::get('/auth/{provider}/callback', function ($provider) {
    $routeName = "social.{$provider}.callback";
    if (Route::has($routeName)) {
        return redirect()->route($routeName);
    }
    return redirect()->route('login')->with('error', 'Social provider not supported.');
})->name('social.callback');

Route::get('/api/social/providers', [App\Http\Controllers\SocialAuthController::class, 'providers'])
    ->name('social.providers');
