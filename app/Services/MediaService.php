<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Centralised, secure media handling. Validation (mime/size/image) is enforced
 * by the Form Requests; this service only stores already-validated files and,
 * where possible, re-encodes raster images to WebP to keep mobile payloads small.
 */
class MediaService
{
    public function __construct(private readonly string $disk = 'public') {}

    /**
     * Store an uploaded image, converting to WebP when the GD extension supports it.
     * Returns the stored path relative to the disk root.
     */
    public function storeImage(UploadedFile $file, string $folder): string
    {
        $webp = $this->encodeWebp($file);

        if ($webp !== null) {
            $path = $folder.'/'.Str::uuid()->toString().'.webp';
            Storage::disk($this->disk)->put($path, $webp);

            return $path;
        }

        // Fallback: store the original with a random, extension-safe name.
        return $file->store($folder, $this->disk);
    }

    /**
     * Store a non-image file (e.g. an uploaded video) as-is.
     */
    public function storeFile(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, $this->disk);
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    /**
     * @return string|null Raw WebP bytes, or null if conversion isn't possible.
     */
    private function encodeWebp(UploadedFile $file): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $mime = $file->getMimeType();
        $source = match ($mime) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($file->getRealPath()) : false,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($file->getRealPath()) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : false,
            default => false, // gif/svg/other: keep original
        };

        if ($source === false || $source === null) {
            return null;
        }

        // Preserve transparency for PNGs.
        imagepalettetotruecolor($source);
        imagealphablending($source, true);
        imagesavealpha($source, true);

        ob_start();
        imagewebp($source, null, 82);
        $data = ob_get_clean();
        imagedestroy($source);

        return $data ?: null;
    }
}
