<div class="space-y-6">
    <!-- Connected Accounts -->
    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Connected Accounts</h3>
        
        @if($user->hasSocialAccounts())
            <div class="space-y-3">
                @foreach($user->socialAccounts as $socialAccount)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-white border border-gray-300 flex items-center justify-center">
                                <i class="{{ $providers[$socialAccount->provider]['icon'] }} text-gray-600"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">
                                    {{ $providers[$socialAccount->provider]['name'] }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    @if($socialAccount->name)
                                        {{ $socialAccount->name }}
                                    @elseif($socialAccount->nickname)
                                        @{{ $socialAccount->nickname }}
                                    @else
                                        Connected Account
                                    @endif
                                </div>
                                @if($socialAccount->email && $socialAccount->email !== $user->email)
                                    <div class="text-xs text-gray-400">{{ $socialAccount->email }}</div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            @if($socialAccount->hasValidToken())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Connected
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Needs Reconnect
                                </span>
                            @endif
                            
                            @if($user->socialAccounts->count() > 1 || $user->password)
                                <form wire:submit="disconnect('{{ $socialAccount->provider }}')" class="inline">
                                    <button type="submit" 
                                            wire:confirm="Are you sure you want to disconnect your {{ $providers[$socialAccount->provider]['name'] }} account?"
                                            class="text-sm text-red-600 hover:text-red-800 font-medium">
                                        Disconnect
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200">
                <i class="fas fa-link text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-600">No social accounts connected</p>
                <p class="text-sm text-gray-500 mt-1">Connect your social accounts for easier login</p>
            </div>
        @endif
    </div>

    <!-- Available Providers -->
    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Connect More Accounts</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($providers as $key => $provider)
                @if(!$user->hasSocialAccount($key) && $provider['configured'])
                    <a href="{{ route('social.link', $key) }}" 
                       class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        <i class="{{ $provider['icon'] }} mr-3 text-gray-600"></i>
                        <span class="font-medium text-gray-700">Connect {{ $provider['name'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
        
        @if(empty(array_filter($providers, fn($p, $k) => $p['configured'] && !$user->hasSocialAccount($k), ARRAY_FILTER_USE_BOTH)))
            <div class="text-center py-4 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-gray-600">All available social accounts are connected</p>
            </div>
        @endif
    </div>

    <!-- Security Notice -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Security Information</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Connected accounts allow you to login without passwords</li>
                        <li>We only store basic profile information and authentication tokens</li>
                        <li>You can disconnect accounts at any time</li>
                        <li>Keep at least one authentication method active</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
