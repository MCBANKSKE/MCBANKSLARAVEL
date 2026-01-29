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
