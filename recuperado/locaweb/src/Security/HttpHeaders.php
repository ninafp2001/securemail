<?php
declare(strict_types=1);

namespace Lab\Webmail\Security;

final class HttpHeaders
{
    public static function applyBaseline(bool $allowTurnstile = false): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        $scriptSrc = "'self'";
        $frameSrc = "'none'";
        $styleSrc = "'self' 'unsafe-inline' https://fonts.googleapis.com";
        $fontSrc = "'self' https://fonts.gstatic.com";

        if ($allowTurnstile) {
            $scriptSrc .= " https://challenges.cloudflare.com";
            $frameSrc = "https://challenges.cloudflare.com";
        }

        header(
            "Content-Security-Policy: default-src 'self'; "
            . "style-src {$styleSrc}; font-src {$fontSrc}; "
            . "script-src {$scriptSrc}; frame-src {$frameSrc}; "
            . "img-src 'self' data:; base-uri 'self'; form-action 'self'"
        );
    }
}
