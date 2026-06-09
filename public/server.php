<?php

/**
 * Router for PHP's built-in server (local dev + ZAP scans).
 * Usage: php -S 127.0.0.1:8000 -t public public/server.php
 */

$publicPath = __DIR__;

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '');

if ($uri !== '/' && file_exists($publicPath . $uri) && ! is_dir($publicPath . $uri)) {
    $path = $publicPath . $uri;
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($path) ?: 'application/octet-stream';

    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'");
    header('Content-Type: ' . $mime);
    readfile($path);

    return true;
}

require_once $publicPath . '/index.php';
