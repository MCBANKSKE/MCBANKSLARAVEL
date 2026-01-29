@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
<div class="w-full max-w-md">
    <div class="login-card p-8">
        <!-- Floating icon -->
        <div class="flex justify-center mb-6 floating">
            <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-key text-2xl"></i>
            </div>
        </div>
        
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-1">Reset Password</h2>
        <p class="text-center text-gray-600 mb-6">Enter your email to receive a reset link</p>
        
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('status') }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        class="input-field w-full pl-10 pr-3 py-3 rounded-lg focus:outline-none focus:border-yellow-400 @error('email') border-red-500 @enderror" 
                        placeholder="your@email.com"
                        required 
                        autocomplete="email" 
                        autofocus
                    >
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">
                        <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                    </p>
                @enderror
            </div>
            
            <!-- Submit Button -->
            <div>
                <button 
                    type="submit" 
                    class="btn-login bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-6 rounded-lg transition w-full"
                >
                    <i class="fas fa-paper-plane mr-2"></i> Send Reset Link
                </button>
            </div>
            
            <!-- Back to Login Link -->
            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-sm text-yellow-600 hover:text-yellow-700 font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Login
                </a>
            </div>
        </form>
    </div>
    
    <!-- Animated circles decoration -->
    <div class="absolute top-20 left-20 w-40 h-40 rounded-full bg-pink-400 opacity-10 -z-10"></div>
    <div class="absolute bottom-20 right-20 w-60 h-60 rounded-full bg-blue-400 opacity-10 -z-10"></div>
</div>
@endsection