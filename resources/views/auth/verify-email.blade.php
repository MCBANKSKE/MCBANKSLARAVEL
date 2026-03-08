<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - MCBANKS LARAVEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Verify Your Email
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Please verify your email address to continue.
                </p>
            </div>

            <div class="mt-8 bg-white shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    
                    <p class="text-gray-700 mb-6">
                        We've sent a verification link to your email address. 
                        Please check your inbox and click the link to verify your account.
                    </p>

                    @if (session('status'))
                        <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                        @csrf
                        <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Resend Verification Email
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="{{ route('central.logout') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            Log out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
