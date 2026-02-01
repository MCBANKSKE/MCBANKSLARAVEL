<div class="space-y-4">
    <!-- Current Avatar -->
    <div class="flex justify-center">
        <div class="relative">
            <img
                src="{{ $previewUrl ?? asset('images/default-avatar.png') }}"
                alt="Profile Avatar"
                class="w-32 h-32 rounded-full object-cover border-4 border-gray-200"
            />
            @if($isUploading)
                <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center">
                    <div class="text-white text-sm">Uploading...</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Upload Progress -->
    @if($isUploading)
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                 style="width: {{ $uploadProgress }}%"></div>
        </div>
        <p class="text-center text-sm text-gray-600">{{ $uploadProgress }}% complete</p>
    @endif

    <!-- Upload Form -->
    @if(!$isUploading)
        @if($avatar)
            <!-- Preview New Avatar -->
            <div class="space-y-3">
                <div class="flex justify-center">
                    <img
                        src="{{ $avatar->temporaryUrl() }}"
                        alt="Avatar Preview"
                        class="w-32 h-32 rounded-full object-cover border-4 border-blue-200"
                    />
                </div>
                <div class="flex justify-center space-x-3">
                    <button
                        wire:click="uploadAvatar"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Upload Avatar
                    </button>
                    <button
                        wire:click="cancelUpload"
                        class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        @else
            <!-- Upload Button -->
            <div class="space-y-3">
                <label class="block">
                    <span class="sr-only">Choose avatar file</span>
                    <input
                        type="file"
                        wire:model="avatar"
                        accept="image/*"
                        class="block w-full text-sm text-gray-500
                               file:mr-4 file:py-2 file:px-4
                               file:rounded-full file:border-0
                               file:text-sm file:font-semibold
                               file:bg-blue-50 file:text-blue-700
                               hover:file:bg-blue-100
                               cursor-pointer"
                    />
                </label>
                
                @error('avatar')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <p class="text-xs text-gray-500">
                    Supported formats: {{ $supportedFormats }}<br>
                    Maximum file size: {{ $maxFileSize }}
                </p>
            </div>
        @endif
    @endif

    <!-- Remove Avatar Button -->
    @if($hasAvatar && !$isUploading && !$avatar)
        <div class="pt-4 border-t border-gray-200">
            <button
                wire:click="removeAvatar"
                wire:confirm="Are you sure you want to remove your avatar?"
                class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            >
                Remove Avatar
            </button>
        </div>
    @endif
</div>

<!-- Listen for upload progress events -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('upload-progress', (event) => {
            // Progress is handled by Livewire's reactive properties
        });
    });
</script>
