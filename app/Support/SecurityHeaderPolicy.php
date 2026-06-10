<?php

namespace App\Support;

class SecurityHeaderPolicy
{
    public static function contentSecurityPolicy(?string $nonce = null): string
    {
        $scriptSrc = $nonce !== null && $nonce !== ''
            ? "script-src 'self' 'nonce-{$nonce}'"
            : "script-src 'self'";

        $styleSrc = $nonce !== null && $nonce !== ''
            ? "style-src 'self' 'nonce-{$nonce}'"
            : "style-src 'self'";

        return implode('; ', [
            "default-src 'self'",
            $scriptSrc,
            $styleSrc,
            "font-src 'self'",
            "img-src 'self'",
            "connect-src 'self' https://api.open-meteo.com",
            "frame-src 'none'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "worker-src 'self'",
            "manifest-src 'self'",
        ]);
    }

    public static function staticContentSecurityPolicy(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self'",
            "font-src 'self'",
            "img-src 'self'",
            "connect-src 'self'",
            "frame-src 'none'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "worker-src 'self'",
            "manifest-src 'self'",
        ]);
    }
}
