<?php

namespace App\Services;

use App\Models\Avatar;
use App\Models\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AvatarUploadService
{
    protected ImageManager $manager;
    protected array $config;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
        $this->config = [
            'max_size' => Avatar::getMaxFileSize(),
            'allowed_types' => Avatar::getSupportedMimeTypes(),
            'avatar_size' => 300,
            'thumbnail_size' => 100,
            'disk' => 'public',
            'path' => 'avatars',
            'thumbnail_path' => 'avatars/thumbnails',
        ];
    }

    /**
     * Upload and process an avatar image.
     */
    public function uploadAvatar(UploadedFile $file, Profile $profile): Avatar
    {
        $this->validateFile($file);

        // Process the image
        $image = $this->manager->read($file);
        
        // Get original dimensions
        $originalWidth = $image->width();
        $originalHeight = $image->height();

        // Create avatar (square 300x300)
        $avatarImage = $this->createSquareImage($image, $this->config['avatar_size']);
        
        // Create thumbnail (square 100x100)
        $thumbnailImage = $this->createSquareImage($image, $this->config['thumbnail_size']);

        // Generate unique filename
        $fileName = $this->generateFileName($file);
        
        // Paths
        $avatarPath = $this->config['path'] . '/' . $fileName;
        $thumbnailPath = $this->config['thumbnail_path'] . '/' . $fileName;

        // Store files
        $this->storeImage($avatarImage, $avatarPath, $this->config['disk']);
        $this->storeImage($thumbnailImage, $thumbnailPath, $this->config['disk']);

        // Delete old avatar if exists
        $oldAvatar = $profile->avatar;
        if ($oldAvatar) {
            $oldAvatar->delete();
        }

        // Create avatar record
        $avatar = Avatar::create([
            'profile_id' => $profile->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $avatarPath,
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'width' => $this->config['avatar_size'],
            'height' => $this->config['avatar_size'],
            'disk' => $this->config['disk'],
        ]);

        return $avatar;
    }

    /**
     * Validate the uploaded file.
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > $this->config['max_size']) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed size of ' . $this->formatBytes($this->config['max_size']));
        }

        // Check file type
        if (!in_array($file->getMimeType(), $this->config['allowed_types'])) {
            throw new \InvalidArgumentException('File type not allowed. Allowed types: ' . implode(', ', $this->config['allowed_types']));
        }
    }

    /**
     * Create a square image from the original.
     */
    protected function createSquareImage($image, int $size): \Intervention\Image\Image
    {
        $width = $image->width();
        $height = $image->height();

        // Calculate crop dimensions
        if ($width > $height) {
            // Landscape: crop from center
            $cropWidth = $height;
            $cropHeight = $height;
            $cropX = ($width - $height) / 2;
            $cropY = 0;
        } else {
            // Portrait or square: crop from center
            $cropWidth = $width;
            $cropHeight = $width;
            $cropX = 0;
            $cropY = ($height - $width) / 2;
        }

        // Crop and resize
        return $image
            ->crop($cropWidth, $cropHeight, $cropX, $cropY)
            ->resize($size, $size);
    }

    /**
     * Store the image to storage.
     */
    protected function storeImage($image, string $path, string $disk): void
    {
        $encoded = $image->toJpeg(quality: 90);
        Storage::disk($disk)->put($path, $encoded);
    }

    /**
     * Generate a unique filename.
     */
    protected function generateFileName(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return uniqid('avatar_', true) . '.' . $extension;
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get default avatar URL.
     */
    public function getDefaultAvatarUrl(): string
    {
        // Return a data URI SVG as fallback
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg width="300" height="300" viewBox="0 0 300 300" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="150" cy="150" r="150" fill="#E5E7EB"/>
              <circle cx="150" cy="120" r="40" fill="#9CA3AF"/>
              <path d="M150 180C100 180 60 210 60 250V300H240V250C240 210 200 180 150 180Z" fill="#9CA3AF"/>
            </svg>'
        );
    }

    /**
     * Get avatar URL for a profile.
     */
    public function getAvatarUrl(Profile $profile): string
    {
        $avatar = $profile->avatar;
        
        if ($avatar) {
            return $avatar->url;
        }

        return $this->getDefaultAvatarUrl();
    }

    /**
     * Get thumbnail URL for a profile.
     */
    public function getThumbnailUrl(Profile $profile): string
    {
        $avatar = $profile->avatar;
        
        if ($avatar) {
            return $avatar->thumbnail_url;
        }

        return $this->getDefaultAvatarUrl();
    }

    /**
     * Create directories if they don't exist.
     */
    public function ensureDirectoriesExist(): void
    {
        $disk = Storage::disk($this->config['disk']);
        
        if (!$disk->exists($this->config['path'])) {
            $disk->makeDirectory($this->config['path']);
        }
        
        if (!$disk->exists($this->config['thumbnail_path'])) {
            $disk->makeDirectory($this->config['thumbnail_path']);
        }
    }
}
