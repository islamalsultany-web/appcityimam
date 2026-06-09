<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->remove('X-Powered-By');

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $nonce = (string) $request->attributes->get('csp_nonce', '');
        $scriptSrc = $nonce !== '' ? "script-src 'self' 'nonce-{$nonce}'" : "script-src 'self'";
        // style attributes (style="...") in Blade still need unsafe-inline; scripts are strict via nonce.
        $styleSrc = $nonce !== ''
            ? "style-src 'self' 'nonce-{$nonce}' 'unsafe-inline'"
            : "style-src 'self' 'unsafe-inline'";

        $csp = implode('; ', [
            "default-src 'self'",
            $scriptSrc,
            $styleSrc,
            "font-src 'self' data:",
            "img-src 'self' data:",
            "connect-src 'self' https://api.open-meteo.com",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
