<?php
declare(strict_types=1);

/** Cabecalhos e sinais anti-phishing / anti-embed. */
function anti_phishing_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
}

function anti_phishing_enforce(array $config): void
{
    if (empty($config['anti_phishing']) || !is_array($config['anti_phishing'])) {
        anti_phishing_headers();
        return;
    }
    $opts = $config['anti_phishing'];
    if (empty($opts['enabled'])) {
        return;
    }
    anti_phishing_headers();
}
