{{-- 
LOGIN FORM BLADE TEMPLATE
==========================

CUSTOMIZATION INSTRUCTIONS:
---------------------------
1. BASIC SETUP: This template works out of the box with email/password login
2. STYLING: Update CSS classes to match your design system
3. VALIDATION: Error handling is built-in with @error directives
4. REDIRECTION: Role-based redirection is handled in the PHP component
5. REMEMBER ME: Checkbox is optional - remove if not needed

COMPATIBILITY:
--------------
- Works with LoginForm.php component
- Supports role-based redirection (admin, member, etc.)
- Email verification requirement for members
- Remember me functionality

TIPS:
-----
- Update form styling to match your design
- Add social login buttons if needed
- Customize error messages in the PHP component
- Test redirection for different user roles
--}}

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    <!-- Header -->
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Welcome Back</h2>
        <p class="text-gray-600 mt-2">Sign in to your account</p>
    </div>

    <!-- Flash Messages -->
    @if (session('status'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('status') }}
        </div>
    @endif

    <!-- Login Form -->
    <form wire:submit.prevent="login">
        <div class="space-y-4">
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    wire:model="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="your@email.com"
                    required
                    autofocus
                >
                @error('email')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    wire:model="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter your password"
                    required
                >
                @error('password')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember Me (OPTIONAL) -->
            {{-- 
                CUSTOMIZATION: Remove this section if you don't need "Remember Me" functionality
            --}}
            <div class="flex items-center">
                <input 
                    id="remember" 
                    type="checkbox" 
                    wire:model="remember"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label for="remember" class="ml-2 block text-sm text-gray-700">
                    Remember me
                </label>
            </div>

            <!-- Login Button -->
            <button 
                type="submit" 
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="login">Sign In</span>
                <span wire:loading wire:target="login">Signing In...</span>
            </button>
        </div>
    </form>

    <!-- Additional Links -->
    <div class="mt-6 space-y-4">
        <!-- Forgot Password Link (OPTIONAL) -->
        {{-- 
            CUSTOMIZATION: Uncomment if you have password reset functionality
            @if (Route::has('password.request'))
                <div class="text-center">
                    <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline text-sm">
                        Forgot your password?
                    </a>
                </div>
            @endif
        --}}

        <!-- Registration Link -->
        @if (Route::has('register'))
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Sign up</a>
                </p>
            </div>
        @endif
    </div>
</div>

{{-- 
ROLE-BASED REDIRECTION NOTES:
============================
This form automatically redirects users based on their roles:

- Admin users: /admin
- Member users: /member (requires email verification)
- Other roles: / (fallback)

To customize redirection:
1. Update getDashboardRoute() method in LoginForm.php
2. Add new role conditions as needed
3. Ensure routes exist in routes/web.php

EXAMPLE CUSTOMIZATIONS:
-----------------------
- Add social login buttons above the form
- Include two-factor authentication
- Add account lockout after failed attempts
- Implement CAPTCHA for security
- Add "Login with Google" button

STYLING TIPS:
------------
- Update colors to match your brand
- Add icons to form fields
- Implement dark mode support
- Add animations and transitions
- Make responsive for mobile devices
--}}