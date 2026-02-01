<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Avatar extends Model
{
    protected $fillable = [
        'profile_id',
        'original_name',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'width',
        'height',
        'disk',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    /**
     * Get the profile that owns the avatar.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the full URL of the avatar.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    /**
     * Get the thumbnail URL (if thumbnail exists).
     */
    public function getThumbnailUrlAttribute(): string
    {
        $thumbnailPath = str_replace('/avatars/', '/avatars/thumbnails/', $this->file_path);
        
        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            return Storage::disk($this->disk)->url($thumbnailPath);
        }

        return $this->url;
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the avatar is a square image.
     */
    public function isSquare(): bool
    {
        return $this->width === $this->height;
    }

    /**
     * Get the aspect ratio of the avatar.
     */
    public function getAspectRatioAttribute(): float
    {
        if ($this->height === 0) {
            return 0;
        }

        return round($this->width / $this->height, 2);
    }

    /**
     * Delete the avatar file from storage.
     */
    public function deleteFiles(): bool
    {
        $deleted = true;

        // Delete main file
        if (Storage::disk($this->disk)->exists($this->file_path)) {
            $deleted = Storage::disk($this->disk)->delete($this->file_path) && $deleted;
        }

        // Delete thumbnail if exists
        $thumbnailPath = str_replace('/avatars/', '/avatars/thumbnails/', $this->file_path);
        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            $deleted = Storage::disk($this->disk)->delete($thumbnailPath) && $deleted;
        }

        return $deleted;
    }

    /**
     * Override the delete method to also delete files.
     */
    public function delete(): bool
    {
        $this->deleteFiles();
        return parent::delete();
    }

    /**
     * Get supported image types.
     */
    public static function getSupportedMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ];
    }

    /**
     * Get maximum file size in bytes.
     */
    public static function getMaxFileSize(): int
    {
        return 5 * 1024 * 1024; // 5MB
    }

    /**
     * Get allowed file extensions.
     */
    public static function getAllowedExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    }
}
