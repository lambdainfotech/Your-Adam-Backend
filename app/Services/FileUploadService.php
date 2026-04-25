<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    protected string $disk = 'public';

    /**
     * Upload a file to a specific directory.
     */
    public function upload(UploadedFile $file, string $directory, ?string $filename = null): string
    {
        $filename = $filename ?? $this->generateFilename($file);
        $path = $file->storeAs($directory, $filename, $this->disk);

        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Upload and return only the path (not full URL).
     */
    public function uploadPath(UploadedFile $file, string $directory, ?string $filename = null): string
    {
        $filename = $filename ?? $this->generateFilename($file);
        $path = $file->storeAs($directory, $filename, $this->disk);

        return parse_url(Storage::disk($this->disk)->url($path), PHP_URL_PATH);
    }

    /**
     * Delete a file by its public URL.
     */
    public function deleteByUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $path = $this->extractPathFromUrl($url);

        if ($path && Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return false;
    }

    /**
     * Delete a file by its storage path.
     */
    public function deleteByPath(string $path): bool
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return false;
    }

    /**
     * Replace an old file with a new one.
     */
    public function replace(?string $oldUrl, UploadedFile $newFile, string $directory): string
    {
        $this->deleteByUrl($oldUrl);
        return $this->upload($newFile, $directory);
    }

    /**
     * Generate a unique filename.
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(8);
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        return Str::slug("{$timestamp}_{$random}_{$originalName}") . ".{$extension}";
    }

    /**
     * Extract storage path from a public URL.
     */
    protected function extractPathFromUrl(string $url): ?string
    {
        $storageUrl = Storage::disk($this->disk)->url('');
        $path = str_replace($storageUrl, '', $url);

        return $path ?: null;
    }
}
