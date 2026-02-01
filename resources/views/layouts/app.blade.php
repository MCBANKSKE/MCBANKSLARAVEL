<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('profile.show') }}" class="text-xl font-bold text-gray-900">
                            {{ config('app.name') }}
                        </a>
                    </div>
                    
                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('profile.show') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('profile.show') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                            Profile
                        </a>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('profile.edit') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                            Edit Profile
                        </a>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <!-- Avatar -->
                            <div class="relative">
                                <img
                                    src="{{ auth()->user()->thumbnail_url }}"
                                    alt="{{ auth()->user()->display_name }}"
                                    class="h-8 w-8 rounded-full object-cover"
                                />
                                @if(auth()->user()->hasCompleteProfile())
                                    <div class="absolute -bottom-1 -right-1 h-3 w-3 bg-green-400 rounded-full border-2 border-white"></div>
                                @endif
                            </div>
                            
                            <!-- User Name -->
                            <div class="hidden md:block">
                                <div class="text-sm font-medium text-gray-900">{{ auth()->user()->display_name }}</div>
                                <div class="text-xs text-gray-500">{{ auth()->user()->roles->pluck('name')->join(', ') ?: 'User' }}</div>
                            </div>

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Notification System -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // Global notification handler
        window.addEventListener('notify', (event) => {
            const notification = event.detail;
            const container = document.getElementById('notification-container');
            
            const notificationEl = document.createElement('div');
            notificationEl.className = `transform transition-all duration-300 ${
                notification.type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'
            } border rounded-lg p-4 shadow-lg max-w-sm`;
            
            notificationEl.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        ${notification.type === 'success' 
                            ? '<svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                            : '<svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                        }
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium ${
                            notification.type === 'success' ? 'text-green-800' : 'text-red-800'
                        }">${notification.message}</p>
                    </div>
                </div>
            `;
            
            container.appendChild(notificationEl);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notificationEl.style.opacity = '0';
                setTimeout(() => {
                    container.removeChild(notificationEl);
                }, 300);
            }, 3000);
        });

        // Livewire event listeners
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', (notification) => {
                window.dispatchEvent(new CustomEvent('notify', { detail: notification }));
            });
        });
    </script>
</body>
</html>
