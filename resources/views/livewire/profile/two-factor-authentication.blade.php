<div>
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                        Two-Factor Authentication
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Add an extra layer of security to your account.
                    </p>
                </div>
                @if($enabled)
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="ml-2 text-sm font-medium text-green-600 dark:text-green-400">Enabled</span>
                    </div>
                @else
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">Disabled</span>
                    </div>
                @endif
            </div>

            <!-- Two-Factor Status -->
            @if($enabled)
                <div class="mt-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                Two-Factor Authentication is Active
                            </h3>
                            <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                <p>Your account is protected with two-factor authentication.</p>
                                @if($lastUsedAt)
                                    <p class="mt-1">Last used: {{ $lastUsedAt }}</p>
                                @endif
                                @if($hasRemainingRecoveryCodes)
                                    <p class="mt-1">Recovery codes remaining: {{ $remainingRecoveryCodesCount }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recovery Codes Section -->
                <div class="mt-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-md font-medium text-gray-900 dark:text-white">Recovery Codes</h4>
                        <div class="space-x-2">
                            <button
                                wire:click="toggleRecoveryCodes"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                @if($showRecoveryCodes)
                                    Hide Codes
                                @else
                                    Show Codes
                                @endif
                            </button>
                            <button
                                wire:click="regenerateRecoveryCodes"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Regenerate
                            </button>
                            <button
                                wire:click="downloadRecoveryCodes"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Download
                            </button>
                        </div>
                    </div>

                    @if($showRecoveryCodes && count($recoveryCodes) > 0)
                        <div class="mt-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                Save these recovery codes in a secure location. Each code can only be used once.
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($recoveryCodes as $code)
                                    <div class="font-mono text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 px-3 py-2 rounded border border-gray-300 dark:border-gray-600">
                                        {{ $code }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Disable Two-Factor -->
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <button
                        wire:click="confirmDisable"
                        class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-600 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 dark:text-red-400 bg-white dark:bg-red-900/20 hover:bg-red-50 dark:hover:bg-red-900/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                        Disable Two-Factor Authentication
                    </button>
                </div>

                <!-- Disable Confirmation Modal -->
                @if($confirmingDisable)
                    <div class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form wire:submit="disableTwoFactor">
                                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
                                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                                    Disable Two-Factor Authentication
                                                </h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        This will remove the additional security layer from your account. Are you sure you want to continue?
                                                    </p>
                                                </div>
                                                <div class="mt-4">
                                                    <label for="disablePassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Confirm your password
                                                    </label>
                                                    <input
                                                        wire:model="disablePassword"
                                                        id="disablePassword"
                                                        type="password"
                                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                                        placeholder="Enter your password"
                                                    >
                                                    @error('disablePassword')
                                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button
                                            type="submit"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                                        >
                                            Disable
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="cancelDisable"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <!-- Enable Two-Factor -->
                @if(!$showQrCode)
                    <div class="mt-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Enable two-factor authentication to add an extra layer of security to your account. You'll need to use an authenticator app like Google Authenticator, Authy, or 1Password.
                        </p>
                        <button
                            wire:click="showSetupForm"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Enable Two-Factor Authentication
                        </button>
                    </div>
                @else
                    <!-- Setup Form -->
                    <div class="mt-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                        Step 1: Scan QR Code
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                        <p>Scan this QR code with your authenticator app:</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center mb-6">
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                {!! $qrCode !!}
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Can't scan the QR code?</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Enter this code manually in your authenticator app:</p>
                            <div class="font-mono text-sm bg-white dark:bg-gray-800 px-3 py-2 rounded border border-gray-300 dark:border-gray-600">
                                {{ $secret }}
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                        Step 2: Enter Verification Code
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                        <p>Enter the 6-digit code from your authenticator app to complete setup:</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form wire:submit="enableTwoFactor">
                            <div class="mb-4">
                                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Authentication Code
                                </label>
                                <input
                                    wire:model="code"
                                    id="code"
                                    type="text"
                                    maxlength="6"
                                    pattern="[0-9]{6}"
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white text-center text-lg tracking-widest"
                                    placeholder="000000"
                                    autofocus
                                >
                                @error('code')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex space-x-3">
                                <button
                                    type="submit"
                                    class="flex-1 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                                >
                                    Enable Two-Factor Authentication
                                </button>
                                <button
                                    type="button"
                                    wire:click="hideSetupForm"
                                    class="flex-1 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
