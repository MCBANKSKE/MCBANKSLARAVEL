@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
                <p class="mt-2 text-gray-600">View and manage your profile information</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Edit Profile
            </a>
        </div>

        <!-- Profile Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Profile Header -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-32"></div>
            
            <div class="px-6 pb-6">
                <!-- Avatar and Basic Info -->
                <div class="flex items-start -mt-16 mb-6">
                    <div class="relative">
                        <img
                            src="{{ auth()->user()->thumbnail_url }}"
                            alt="{{ auth()->user()->display_name }}"
                            class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover"
                        />
                        <div class="absolute bottom-0 right-0 bg-green-400 w-6 h-6 rounded-full border-2 border-white"></div>
                    </div>
                    <div class="ml-6 mt-20">
                        <h2 class="text-2xl font-bold text-gray-900">{{ auth()->user()->display_name }}</h2>
                        <p class="text-gray-600">{{ auth()->user()->email }}</p>
                        @if(auth()->user()->location)
                            <p class="text-sm text-gray-500 mt-1">
                                <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ auth()->user()->location }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Profile Completion -->
                @if(auth()->user()->profile)
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Profile Completion</span>
                            <span class="text-sm font-bold text-gray-900">{{ auth()->user()->profile_completion_percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ auth()->user()->profile_completion_percentage }}%"></div>
                        </div>
                    </div>
                @endif

                <!-- Profile Details -->
                @if(auth()->user()->profile)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Bio -->
                        @if(auth()->user()->profile->bio)
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">About</h3>
                                <p class="text-gray-600">{{ auth()->user()->profile->bio }}</p>
                            </div>
                        @endif

                        <!-- Contact Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Contact Information</h3>
                            <div class="space-y-2">
                                @if(auth()->user()->profile->phone)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        {{ auth()->user()->profile->phone }}
                                    </div>
                                @endif

                                @if(auth()->user()->profile->website)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                        </svg>
                                        <a href="{{ auth()->user()->profile->formatted_website }}" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800">
                                            {{ auth()->user()->profile->website }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Location -->
                        @if(auth()->user()->profile->full_location)
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Location</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ auth()->user()->profile->full_location }}
                                    </div>
                                    @if(auth()->user()->profile->address)
                                        <div class="flex items-start text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                            </svg>
                                            {{ auth()->user()->profile->address }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No profile information</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding some information to your profile.</p>
                        <div class="mt-6">
                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Create Profile
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-3xl font-bold text-blue-600">{{ auth()->user()->profile_completion_percentage }}%</div>
                <div class="text-sm text-gray-600 mt-1">Profile Complete</div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-3xl font-bold text-green-600">
                    {{ auth()->user()->hasCompleteProfile() ? '✓' : '○' }}
                </div>
                <div class="text-sm text-gray-600 mt-1">Profile Status</div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-3xl font-bold text-purple-600">{{ auth()->user()->roles->count() }}</div>
                <div class="text-sm text-gray-600 mt-1">User Roles</div>
            </div>
        </div>
    </div>
</div>
@endsection
