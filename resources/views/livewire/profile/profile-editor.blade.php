<div>
    <!-- Profile Completion Indicator -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Profile Completion</h3>
            <span class="text-2xl font-bold {{ $completionColor }} text-white px-3 py-1 rounded-full">
                {{ $completionPercentage }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
            <div class="h-3 rounded-full transition-all duration-300 {{ $completionColor }}" 
                 style="width: {{ $completionPercentage }}%"></div>
        </div>
        <p class="text-sm text-gray-600">{{ $completionMessage }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Avatar Upload -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Picture</h3>
                <livewire:profile.avatar-upload />
            </div>
        </div>

        <!-- Profile Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <form wire:submit="save">
                    <!-- Basic Information -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            
                            <!-- Bio -->
                            <div class="mb-4">
                                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bio
                                </label>
                                <textarea
                                    id="bio"
                                    wire:model="bio"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Tell us about yourself..."
                                ></textarea>
                                @error('bio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="mb-4">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input
                                    id="phone"
                                    type="tel"
                                    wire:model="phone"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="+1 (555) 123-4567"
                                />
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Website -->
                            <div class="mb-4">
                                <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                                    Website
                                </label>
                                <input
                                    id="website"
                                    type="url"
                                    wire:model="website"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="https://yourwebsite.com"
                                />
                                @error('website')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Country -->
                                <div>
                                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                        Country
                                    </label>
                                    <select
                                        id="country"
                                        wire:model.live="country_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- State -->
                                <div>
                                    <label for="state" class="block text-sm font-medium text-gray-700 mb-2">
                                        State/Province
                                    </label>
                                    <select
                                        id="state"
                                        wire:model.live="state_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        {{ !$country_id ? 'disabled' : '' }}
                                    >
                                        <option value="">Select State</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('state_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- City -->
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                        City
                                    </label>
                                    <select
                                        id="city"
                                        wire:model="city_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        {{ !$state_id ? 'disabled' : '' }}
                                    >
                                        <option value="">Select City</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('city_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="mt-4">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Address
                                </label>
                                <input
                                    id="address"
                                    type="text"
                                    wire:model="address"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="123 Main St, Apt 4B"
                                />
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Privacy Settings -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Privacy Settings</h3>
                            
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="show_email"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Show email address on public profile</span>
                                </label>

                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="show_phone"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Show phone number on public profile</span>
                                </label>

                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="show_location"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Show location on public profile</span>
                                </label>

                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="show_website"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Show website on public profile</span>
                                </label>

                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="allow_messages"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Allow other users to send you messages</span>
                                </label>

                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="profile_public"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Make profile publicly visible</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 flex justify-end">
                        <button
                            type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="{{ $isLoading }}"
                        >
                            <span wire:loading wire:target="save" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                            <span wire:loading.remove wire:target="save">Save Profile</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Notification Toast -->
<div x-data="{ show: $wire.$get('notification')?.type }" 
     x-show="show" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed top-4 right-4 z-50">
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-4 max-w-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg x-show="$wire.$get('notification')?.type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="$wire.$get('notification')?.type === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900" x-text="$wire.$get('notification')?.message"></p>
            </div>
        </div>
    </div>
</div>

<script>
    // Listen for Livewire events
    window.addEventListener('profile-updated', () => {
        // Refresh profile completion indicator
        Livewire.find('{{ $this->getId() }}').call('$refresh');
    });
</script>
