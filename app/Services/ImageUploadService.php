<?php

namespace App\Services;

use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    protected string $disk = 'public';

    /**
     * Upload product images
     *
     * @param int $productId
     * @param array $files
     * @return array
     */
    public function uploadImages(int $productId, array $files): array
    {
        $uploadedImages = [];
        $basePath = "products/{$productId}";

        foreach ($files as $index => $file) {
            $uploadedImages[] = $this->processSingleImage($productId, $file, $basePath, $index);
        }

        return $uploadedImages;
    }

    /**
     * Process a single image upload
     *
     * @param int $productId
     * @param UploadedFile $file
     * @param string $basePath
     * @param int $index
     * @return ProductImage
     */
    protected function processSingleImage(int $productId, UploadedFile $file, string $basePath, int $index): ProductImage
    {
        $filename = $this->generateFilename($file);
        
        // Store original image
        $originalPath = $file->storeAs("{$basePath}/original", $filename, $this->disk);
        
        // Create thumbnails
        $thumbnailPaths = $this->createThumbnails($file, $basePath, $filename);
        
        // Get the next sort order
        $sortOrder = ProductImage::where('product_id', $productId)->max('sort_order') + 1 + $index;
        
        // Check if this is the first image (set as main)
        $isMain = !ProductImage::where('product_id', $productId)->where('is_main', true)->exists();
        
        // Create database record
        return ProductImage::create([
            'product_id' => $productId,
            'image_url' => Storage::url($originalPath),
            'thumbnail_url' => $thumbnailPaths['medium'] ?? null,
            'alt_text' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'is_main' => $isMain,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Generate unique filename
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(8);
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension();
        
        return Str::slug("{$timestamp}_{$random}_{$originalName}") . ".{$extension}";
    }

    /**
     * Create thumbnails for uploaded image using GD
     *
     * @param UploadedFile $file
     * @param string $basePath
     * @param string $filename
     * @return array
     */
    protected function createThumbnails(UploadedFile $file, string $basePath, string $filename): array
    {
        $paths = [];
        $sizes = [
            'medium' => [300, 300],
            'small' => [150, 150],
        ];
        
        $sourcePath = $file->getRealPath();
        $sourceInfo = getimagesize($sourcePath);
        
        if (!$sourceInfo) {
            // Fallback to simple copy if image analysis fails
            foreach ($sizes as $sizeName => [$width, $height]) {
                $file->storeAs("{$basePath}/thumbnails/{$sizeName}", $filename, $this->disk);
                $paths[$sizeName] = Storage::url("{$basePath}/thumbnails/{$sizeName}/{$filename}");
            }
            return $paths;
        }
        
        $sourceWidth = $sourceInfo[0];
        $sourceHeight = $sourceInfo[1];
        $mimeType = $sourceInfo['mime'];
        
        // Create source image
        $sourceImage = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            default => null,
        };
        
        if (!$sourceImage) {
            foreach ($sizes as $sizeName => [$width, $height]) {
                $file->storeAs("{$basePath}/thumbnails/{$sizeName}", $filename, $this->disk);
                $paths[$sizeName] = Storage::url("{$basePath}/thumbnails/{$sizeName}/{$filename}");
            }
            return $paths;
        }
        
        foreach ($sizes as $sizeName => [$width, $height]) {
            // Calculate dimensions preserving aspect ratio
            $ratio = min($width / $sourceWidth, $height / $sourceHeight);
            $newWidth = (int) ($sourceWidth * $ratio);
            $newHeight = (int) ($sourceHeight * $ratio);
            
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG/WebP
            if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
            }
            
            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
            
            $thumbnailFullPath = Storage::disk($this->disk)->path("{$basePath}/thumbnails/{$sizeName}/{$filename}");
            
            // Ensure directory exists
            $dir = dirname($thumbnailFullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            match ($mimeType) {
                'image/jpeg' => imagejpeg($thumbnail, $thumbnailFullPath, 85),
                'image/png' => imagepng($thumbnail, $thumbnailFullPath, 8),
                'image/webp' => imagewebp($thumbnail, $thumbnailFullPath, 85),
                'image/gif' => imagegif($thumbnail, $thumbnailFullPath),
                default => imagejpeg($thumbnail, $thumbnailFullPath, 85),
            };
            
            imagedestroy($thumbnail);
            
            $paths[$sizeName] = Storage::url("{$basePath}/thumbnails/{$sizeName}/{$filename}");
        }
        
        imagedestroy($sourceImage);
        
        return $paths;
    }

    /**
     * Delete image and its thumbnails
     *
     * @param ProductImage $image
     * @return bool
     */
    public function deleteImage(ProductImage $image): bool
    {
        // Delete physical files
        $this->deletePhysicalFiles($image);
        
        // Delete database record
        return $image->delete();
    }

    /**
     * Delete physical files from storage
     *
     * @param ProductImage $image
     * @return void
     */
    protected function deletePhysicalFiles(ProductImage $image): void
    {
        // Extract path from URL
        $storageUrl = Storage::url('');
        
        $originalPath = str_replace($storageUrl, '', $image->image_url);
        
        // Delete original
        if (Storage::disk($this->disk)->exists($originalPath)) {
            Storage::disk($this->disk)->delete($originalPath);
        }
        
        // Delete thumbnails
        $thumbnailPath = str_replace($storageUrl, '', $image->thumbnail_url ?? '');
        if ($thumbnailPath && Storage::disk($this->disk)->exists($thumbnailPath)) {
            Storage::disk($this->disk)->delete($thumbnailPath);
        }
        
        // Delete small thumbnail
        $smallThumbnailPath = str_replace('/medium/', '/small/', $thumbnailPath);
        if ($smallThumbnailPath && Storage::disk($this->disk)->exists($smallThumbnailPath)) {
            Storage::disk($this->disk)->delete($smallThumbnailPath);
        }
    }

    /**
     * Set image as main
     *
     * @param int $productId
     * @param int $imageId
     * @return bool
     */
    public function setAsMain(int $productId, int $imageId): bool
    {
        // Remove main status from all other images
        ProductImage::where('product_id', $productId)
            ->where('is_main', true)
            ->update(['is_main' => false]);
        
        // Set new main image
        return ProductImage::where('id', $imageId)
            ->where('product_id', $productId)
            ->update(['is_main' => true]);
    }

    /**
     * Reorder images
     *
     * @param int $productId
     * @param array $orderData
     * @return void
     */
    public function reorderImages(int $productId, array $orderData): void
    {
        foreach ($orderData as $index => $imageId) {
            ProductImage::where('id', $imageId)
                ->where('product_id', $productId)
                ->update(['sort_order' => $index]);
        }
    }

    /**
     * Update image alt text
     *
     * @param ProductImage $image
     * @param string|null $altText
     * @return bool
     */
    public function updateAltText(ProductImage $image, ?string $altText): bool
    {
        $image->alt_text = $altText;
        return $image->save();
    }

    /**
     * Get images for product
     *
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductImages(int $productId)
    {
        return ProductImage::where('product_id', $productId)
            ->orderBy('sort_order')
            ->get();
    }
}
