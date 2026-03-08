<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controllers
use App\Http\Controllers\Auth\CentralAuthController;


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Centralized Authentication Routes (Role-based)
Route::middleware('guest')->group(function () {
    Route::controller(CentralAuthController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('central.login');
        Route::post('/login', 'login')->name('central.login.post');
        Route::get('/register', 'showRegistrationForm')->name('central.register');
        Route::post('/register', 'register')->name('central.register.post');
        
        // Password Reset Routes
        Route::get('/forgot-password', 'showPasswordRequestForm')->name('password.request');
        Route::post('/forgot-password', 'sendResetLinkEmail')->name('password.email');
        Route::get('/reset-password/{token}', 'showResetForm')->name('password.reset');
        Route::post('/reset-password', 'reset')->name('password.update');
        
        // Google OAuth with role selection
        Route::get('/auth/google', 'googleRedirect')->name('central.google');
        Route::get('/auth/google/callback', 'googleCallback')->name('central.google.callback');
    });
});

// Logout (Authenticated only)
Route::middleware('auth')->post('logout', [CentralAuthController::class, 'logout'])->name('central.logout');

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
| Role-based Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Admin Dashboard
    Route::get('/admin', function () {
        return '<h1>Admin Dashboard</h1><p>Welcome Admin! Your dashboard will be implemented here.</p>';
    })->name('admin.dashboard');

    // Host Dashboard
    Route::get('/host', function () {
        return '<h1>Host Dashboard</h1><p>Welcome Host! Your dashboard will be implemented here.</p>';
    })->name('host.dashboard');

    // Guest Dashboard
    Route::get('/guest', function () {
        return '<h1>Guest Dashboard</h1><p>Welcome Guest! Your dashboard will be implemented here.</p>';
    })->name('guest.dashboard');
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

