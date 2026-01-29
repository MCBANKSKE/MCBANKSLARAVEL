{{-- 
REGISTRATION FORM BLADE TEMPLATE
================================

CUSTOMIZATION INSTRUCTIONS:
---------------------------
1. BASIC SETUP: This template works out of the box with minimal fields
2. ADD FIELDS: Uncomment sections you need (personal info, location, etc.)
3. REMOVE FIELDS: Delete entire sections you don't need
4. STYLING: Update CSS classes to match your design
5. VALIDATION: Ensure your PHP component has matching validation rules

FIELD SECTIONS:
---------------
- REQUIRED: User account info (name, email, password)
- OPTIONAL: Personal information (gender, date of birth)
- OPTIONAL: Location information (country, state, city, address)
- OPTIONAL: Contact information (phone)
- REQUIRED: Terms acceptance (for legal compliance)

STEP STRUCTURE:
--------------
- Step 1: Basic account information (always required)
- Step 2: Optional profile information (customize as needed)
- Step 3: Terms and final submission (always required)

TIPS:
-----
- Remove entire @if($currentStep === X) blocks you don't need
- Add @error directives for validation feedback
- Update wire:model names to match your PHP component properties
- Test with different field combinations to ensure validation works
--}}

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    <!-- Header -->
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Create Account</h2>
        <p class="text-gray-600 mt-2">Join us today</p>
    </div>

    <!-- Flash Messages -->
    @if (session('status'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit.prevent="{{ $currentStep === 2 ? 'register' : 'nextStep' }}">
        
        {{-- STEP 1: BASIC ACCOUNT INFORMATION (ALWAYS REQUIRED) --}}
        @if($currentStep === 1)
            <div class="space-y-4">
                <!-- Name Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input 
                        type="text" 
                        wire:model="user.name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter your full name"
                        required
                    >
                    @error('user.name')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input 
                        type="email" 
                        wire:model="user.email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="your@email.com"
                        required
                    >
                    @error('user.email')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input 
                        type="password" 
                        wire:model="user.password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Create a password"
                        required
                    >
                    @error('user.password')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input 
                        type="password" 
                        wire:model="user.password_confirmation"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Confirm your password"
                        required
                    >
                </div>

                <!-- Role Selection (OPTIONAL - Uncomment if you want role selection) -->
                {{-- 
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                    <select 
                        wire:model="selected_role"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="member">Member</option>
                        <option value="customer">Customer</option>
                        <option value="manager">Manager</option>
                    </select>
                    @error('selected_role')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                --}}

                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
                    Continue
                </button>
            </div>
        @endif

        {{-- STEP 2: OPTIONAL PROFILE INFORMATION --}}
        {{-- 
            CUSTOMIZATION: 
            - Remove this entire @if block if you don't need profile information
            - Uncomment only the fields you need
            - Add new fields following the same pattern
            - Update validation rules in your PHP component accordingly
        --}}
        @if($currentStep === 2)
            <div class="space-y-4">
                <!-- Personal Information Section (OPTIONAL) -->
                {{-- 
                <div class="border-b pb-4 mb-4">
                    <h3 class="text-lg font-medium mb-3">Personal Information</h3>
                    
                    <!-- Gender Field (OPTIONAL) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select 
                            wire:model="gender"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        @error('gender')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Date of Birth Field (OPTIONAL) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                        <input 
                            type="date" 
                            wire:model="date_of_birth"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            max="{{ now()->subYears(18)->format('Y-m-d') }}"
                        >
                        @error('date_of_birth')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                --}}

                <!-- Location Information Section (OPTIONAL - Requires Country/State models) -->
                {{-- 
                <div class="border-b pb-4 mb-4">
                    <h3 class="text-lg font-medium mb-3">Location Information</h3>
                    
                    <!-- Country Field (OPTIONAL - Requires Country model) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <select 
                            wire:model.live="nationality_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select country</option>
                            @if(isset($countries))
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('nationality_id')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- State Field (OPTIONAL - Requires State model) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
                        <select 
                            wire:model="state_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            {{ empty($states) ? 'disabled' : '' }}
                        >
                            <option value="">Select state</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                        @error('state_id')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- City Field (OPTIONAL) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input 
                            type="text" 
                            wire:model="city"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your city"
                        >
                        @error('city')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Address Field (OPTIONAL) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea 
                            wire:model="address"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your address"
                            rows="3"
                        ></textarea>
                        @error('address')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                --}}

                <!-- Contact Information Section (OPTIONAL) -->
                {{-- 
                <div class="mb-4">
                    <h3 class="text-lg font-medium mb-3">Contact Information</h3>
                    
                    <!-- Phone Field (OPTIONAL) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input 
                            type="tel" 
                            wire:model="phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="+1 (555) 123-4567"
                        >
                        @error('phone')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                --}}

                <!-- Navigation Buttons -->
                <div class="flex justify-between space-x-4">
                    <button 
                        type="button" 
                        wire:click="previousStep"
                        class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition"
                    >
                        Back
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        Continue
                    </button>
                </div>
            </div>
        @endif

        {{-- STEP 3: TERMS AND SUBMISSION (ALWAYS REQUIRED) --}}
        @if($currentStep === 3)
            <div class="space-y-4">
                <!-- Terms and Conditions -->
                <div class="bg-gray-50 p-4 rounded-md">
                    <h3 class="text-lg font-medium mb-2">Terms & Conditions</h3>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>By creating an account, you agree to our Terms of Service and Privacy Policy.</p>
                        <p>You must be at least 13 years old to create an account.</p>
                        <p>You are responsible for maintaining the confidentiality of your account.</p>
                    </div>
                    
                    <!-- Terms Checkbox (REQUIRED) -->
                    <div class="mt-4">
                        <label class="flex items-start">
                            <input 
                                type="checkbox" 
                                wire:model="terms"
                                class="mt-1 mr-2"
                                required
                            >
                            <span class="text-sm text-gray-700">I agree to the Terms of Service and Privacy Policy</span>
                        </label>
                        @error('terms')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between space-x-4">
                    <button 
                        type="button" 
                        wire:click="previousStep"
                        class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition"
                    >
                        Back
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="register">Create Account</span>
                        <span wire:loading wire:target="register">Creating...</span>
                    </button>
                </div>
            </div>
        @endif
    </form>

    <!-- Progress Indicator -->
    <div class="mt-6 flex justify-center space-x-2">
        @foreach([1, 2, 3] as $step)
            <div class="h-2 w-8 rounded-full {{ $currentStep >= $step ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
        @endforeach
    </div>

    <!-- Login Link -->
    @if($currentStep === 1)
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Sign in</a>
            </p>
        </div>
    @endif
</div>

{{-- 
CUSTOMIZATION NOTES:
==================
1. SIMPLE REGISTRATION: Keep only Step 1 and Step 3 (remove Step 2 entirely)
2. FULL REGISTRATION: Uncomment all optional fields in Step 2
3. PARTIAL REGISTRATION: Selectively uncomment fields you need
4. STYLING: Update CSS classes to match your design system
5. VALIDATION: Ensure PHP component has matching validation rules
6. MODELS: Uncomment Country/State sections only if you have those models

EXAMPLE CONFIGURATIONS:
-----------------------
- Minimal: Step 1 + Step 3 only
- Basic: Step 1 + Step 3 + Phone field
- Standard: Step 1 + Step 3 + Personal Info + Phone
- Complete: All fields uncommented

TESTING:
-------
- Test with different field combinations
- Verify validation works for each configuration
- Ensure form submission works correctly
- Check error handling and user feedback
--}}