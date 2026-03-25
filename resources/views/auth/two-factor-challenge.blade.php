<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - MCBANKS LARAVEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                    Two-Factor Authentication
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    Enter the authentication code from your app
                </p>
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

            <form class="mt-8 space-y-6" action="{{ route('2fa.verify') }}" method="POST">
                @csrf
                
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Authentication Code
                    </label>
                    <div class="mt-1">
                        <input 
                            id="code" 
                            name="code" 
                            type="text" 
                            maxlength="6" 
                            pattern="[0-9]{6}"
                            required
                            class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm text-center text-lg tracking-widest dark:bg-gray-700 dark:border-gray-600"
                            placeholder="000000"
                            autofocus
                            autocomplete="one-time-code"
                        >
                    </div>
                    @error('code')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-shield-alt h-5 w-5 text-indigo-500 group-hover:text-indigo-400"></i>
                        </span>
                        Verify Code
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
                    <a href="{{ route('2fa.recovery') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas fa-key h-5 w-5 mr-2"></i>
                        Use a recovery code
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
        // Auto-focus and format input
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('code');
            
            input.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Auto-submit when 6 digits are entered
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
                this.value = pastedData.slice(0, 6);
                
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
        });
    </script>
</body>
</html>
