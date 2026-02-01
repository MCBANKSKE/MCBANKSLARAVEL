@if($showLabel)
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Or continue with</label>
    </div>
@endif

<div class="space-y-3">
    @foreach($providers as $key => $provider)
        @if($provider['configured'])
            <a href="{{ route('social.redirect', $key) }}" 
               class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <i class="{{ $provider['icon'] }} mr-2"></i>
                Continue with {{ $provider['name'] }}
            </a>
        @endif
    @endforeach
</div>

@if($showDivider)
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">or</span>
        </div>
    </div>
@endif

@if($showCompact)
    <div class="flex space-x-2">
        @foreach($providers as $key => $provider)
            @if($provider['configured'])
                <a href="{{ route('social.redirect', $key) }}" 
                   class="flex-1 flex items-center justify-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                   title="Continue with {{ $provider['name'] }}">
                    <i class="{{ $provider['icon'] }}"></i>
                </a>
            @endif
        @endforeach
    </div>
@endif

@if($showIconsOnly)
    <div class="flex space-x-3">
        @foreach($providers as $key => $provider)
            @if($provider['configured'])
                <a href="{{ route('social.redirect', $key) }}" 
                   class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-300 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                   title="Continue with {{ $provider['name'] }}">
                    <i class="{{ $provider['icon'] }} text-gray-600"></i>
                </a>
            @endif
        @endforeach
    </div>
@endif
