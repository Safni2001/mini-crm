<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;

class FileUploadService
{
    protected string $disk = 'public';
    protected string $logoDirectory = 'logos';

    public function uploadCompanyLogo(UploadedFile $file, ?string $oldLogoPath = null): string
    {
        // Delete old logo if exists
        if ($oldLogoPath) {
            $this->deleteFile($oldLogoPath);
        }

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);

        // Store the file
        $path = $file->storeAs($this->logoDirectory, $filename, $this->disk);

        // Optionally resize/optimize the image
        $this->optimizeImage($path);

        return $path;
    }

    public function deleteFile(string $path): bool
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return true;
    }

    public function getFileUrl(string $path): ?string
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->url($path);
        }

        return null;
    }

    public function fileExists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    public function getFileSize(string $path): int
    {
        if ($this->fileExists($path)) {
            return Storage::disk($this->disk)->size($path);
        }

        return 0;
    }

    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $timestamp = now()->timestamp;
        $randomString = Str::random(8);
        $extension = $file->getClientOriginalExtension();

        return "company_logo_{$timestamp}_{$randomString}.{$extension}";
    }

    protected function optimizeImage(string $path): void
    {
        try {
            $fullPath = Storage::disk($this->disk)->path($path);

            // Skip optimization if Intervention Image is not installed
            if (!class_exists(ImageManager::class)) {
                return;
            }

            $manager = new ImageManager(new Driver());
            $image = $manager->read($fullPath);

            // Resize if image is too large (max 800x800 while maintaining aspect ratio)
            if ($image->width() > 800 || $image->height() > 800) {
                $image->scale(width: 800, height: 800);
            }

            // Save optimized image
            $image->save($fullPath, quality: 85);

        } catch (\Exception $e) {
            // Log error but don't fail the upload
            Log::warning('Image optimization failed: ' . $e->getMessage());
        }
    }

    public function getUploadConstraints(): array
    {
        return [
            'max_size_mb' => 2,
            'min_width' => 100,
            'min_height' => 100,
            'allowed_types' => ['jpeg', 'png', 'jpg', 'gif'],
            'max_width' => 2000,
            'max_height' => 2000,
        ];
    }

    public function validateImageDimensions(UploadedFile $file): array
    {
        $constraints = $this->getUploadConstraints();
        $errors = [];

        try {
            $imageSize = getimagesize($file->getPathname());
            if (!$imageSize) {
                $errors[] = 'Invalid image file.';
                return $errors;
            }

            [$width, $height] = $imageSize;

            if ($width < $constraints['min_width'] || $height < $constraints['min_height']) {
                $errors[] = "Image must be at least {$constraints['min_width']}x{$constraints['min_height']} pixels.";
            }

            if ($width > $constraints['max_width'] || $height > $constraints['max_height']) {
                $errors[] = "Image must not exceed {$constraints['max_width']}x{$constraints['max_height']} pixels.";
            }

        } catch (\Exception $e) {
            $errors[] = 'Unable to process image file.';
        }

        return $errors;
    }
}