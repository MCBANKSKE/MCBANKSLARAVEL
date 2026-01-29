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
        
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-1">Reset Your Password</h2>
        <p class="text-center text-gray-600 mb-6">Enter your new password below</p>
        
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <!-- Email Field (hidden if email is pre-filled) -->
            @if(empty($email))
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
                        value="{{ $email ?? old('email') }}" 
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
            @else
                <input type="hidden" name="email" value="{{ $email }}">
            @endif
            
            <!-- New Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="input-field w-full pl-10 pr-10 py-3 rounded-lg focus:outline-none focus:border-yellow-400 @error('password') border-red-500 @enderror" 
                        placeholder="••••••••"
                        required 
                        autocomplete="new-password"
                        @if(empty($email)) autofocus @endif
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword('password')">
                        <i class="fas fa-eye text-gray-400 hover:text-gray-500"></i>
                    </div>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">
                        <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                    </p>
                @enderror
            </div>
            
            <!-- Confirm Password Field -->
            <div class="mb-2">
                <label for="password-confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input 
                        type="password" 
                        id="password-confirm" 
                        name="password_confirmation" 
                        class="input-field w-full pl-10 pr-10 py-3 rounded-lg focus:outline-none focus:border-yellow-400" 
                        placeholder="••••••••"
                        required 
                        autocomplete="new-password"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword('password-confirm')">
                        <i class="fas fa-eye text-gray-400 hover:text-gray-500"></i>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="pt-2">
                <button 
                    type="submit" 
                    class="btn-login bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-6 rounded-lg transition w-full"
                >
                    <i class="fas fa-redo mr-2"></i> Reset Password
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

@push('scripts')
<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = field.nextElementSibling.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endpush
@endsection
