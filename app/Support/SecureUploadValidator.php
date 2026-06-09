<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class SecureUploadValidator
{
    /** @var array<string, list<string>> */
    private const MIME_MAP = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'doc' => ['application/msword'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/x-zip-compressed',
        ],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'application/x-zip-compressed',
        ],
    ];

    /** @var list<string> */
    private const BLOCKED_MIMES = [
        'text/html',
        'application/xhtml+xml',
        'application/x-httpd-php',
        'application/x-php',
        'text/x-php',
        'application/javascript',
        'text/javascript',
    ];

    public static function isAllowed(UploadedFile $file, string $allowedMimesConfig): bool
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $allowedExtensions = array_filter(array_map('trim', explode(',', strtolower($allowedMimesConfig))));

        if ($extension === '' || ! in_array($extension, $allowedExtensions, true)) {
            return false;
        }

        $detectedMime = self::detectMimeType($file);

        if ($detectedMime === null || in_array($detectedMime, self::BLOCKED_MIMES, true)) {
            return false;
        }

        $allowedMimes = self::MIME_MAP[$extension] ?? [];

        if ($allowedMimes !== [] && ! in_array($detectedMime, $allowedMimes, true)) {
            return false;
        }

        return self::matchesSignature($file, $extension);
    }

    public static function failureMessage(): string
    {
        return 'الملف المرفوع غير صالح أو نوعه غير مسموح.';
    }

    private static function detectMimeType(UploadedFile $file): ?string
    {
        $path = $file->getRealPath();

        if ($path === false) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path);

        return is_string($mime) ? strtolower($mime) : null;
    }

    private static function matchesSignature(UploadedFile $file, string $extension): bool
    {
        $path = $file->getRealPath();

        if ($path === false) {
            return false;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        $header = fread($handle, 8) ?: '';
        fclose($handle);

        return match ($extension) {
            'pdf' => str_starts_with($header, '%PDF-'),
            'jpg', 'jpeg' => str_starts_with($header, "\xFF\xD8\xFF"),
            'png' => str_starts_with($header, "\x89PNG\r\n\x1A\n"),
            'doc' => str_starts_with($header, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"),
            'docx', 'xlsx' => str_starts_with($header, "PK\x03\x04"),
            'xls' => str_starts_with($header, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")
                || str_starts_with($header, "PK\x03\x04"),
            default => false,
        };
    }
}
