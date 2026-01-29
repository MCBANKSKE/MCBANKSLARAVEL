<x-guest-layout>
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Verify Your Email Address</h1>
        
        <div class="mb-6 text-sm text-gray-600">
            <p>Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.</p>
            <p class="mt-2">If you didn't receive the email, click the button below to request another one.</p>
        </div>
  
        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-md">
                {{ __('A new verification link has been sent to your email address.') }}
            </div>
        @endif

        <div class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4 justify-center">
            <form method="POST" action="{{ route('verification.send') }}" class="text-center">
                @csrf
                <x-button.primary>
                    {{ __('Resend Verification Email') }}
                </x-button.primary>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="text-center">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Log Out') }}
                </button>
        </form>
    </div>
</x-guest-layout>
