<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery Code - MCBANKS LARAVEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                    Recovery Code
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    Enter one of your recovery codes
                </p>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            Using a Recovery Code
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <p>Recovery codes should only be used when you can't access your authenticator app. Each code can only be used once.</p>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-600 px-4 py-3 rounded-lg">
                    {{ session('warning') }}
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('2fa.recovery.verify') }}" method="POST">
                @csrf
                
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Recovery Code
                    </label>
                    <div class="mt-1">
                        <input 
                            id="code" 
                            name="code" 
                            type="text" 
                            maxlength="10"
                            required
                            class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm text-center font-mono text-lg dark:bg-gray-700 dark:border-gray-600"
                            placeholder="XXXX-XXXX"
                            autocomplete="off"
                        >
                    </div>
                    @error('code')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Enter the code exactly as shown, including the dash
                    </p>
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-key h-5 w-5 text-yellow-500 group-hover:text-yellow-400"></i>
                        </span>
                        Use Recovery Code
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500 dark:bg-gray-900 dark:text-gray-400">Or</span>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('2fa.challenge') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas fa-mobile-alt h-5 w-5 mr-2"></i>
                        Use authentication app
                    </a>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('2fa.logout') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                    <i class="fas fa-sign-out-alt mr-1"></i>
                    Sign out
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-format input
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('code');
            
            input.addEventListener('input', function(e) {
                let value = this.value.toUpperCase();
                
                // Auto-add dash after 4 characters
                if (value.length === 4 && !value.includes('-')) {
                    value += '-';
                }
                
                // Remove extra dashes and limit to 9 characters (XXXX-XXXX)
                value = value.replace(/-/g, '').slice(0, 8);
                if (value.length > 4) {
                    value = value.slice(0, 4) + '-' + value.slice(4);
                }
                
                this.value = value;
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                let pastedData = e.clipboardData.getData('text').toUpperCase().replace(/[^A-Z0-9]/g, '');
                
                if (pastedData.length >= 8) {
                    pastedData = pastedData.slice(0, 4) + '-' + pastedData.slice(4, 8);
                }
                
                this.value = pastedData;
            });
        });
    </script>
</body>
</html>
