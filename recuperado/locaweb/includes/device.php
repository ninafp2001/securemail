<?php
declare(strict_types=1);

require_once __DIR__ . '/compat.php';

function parse_device(string $ua): array
{
    $ua = $ua ?: 'unknown';

    if (device_ua_looks_bot($ua)) {
        return [
            'os'      => 'Bot',
            'browser' => device_parse_browser($ua),
            'icon'    => '🤖',
            'kind'    => 'bot',
        ];
    }

    $kind = 'desktop';
    $os = 'Desconhecido';
    $icon = '💻';

    if (preg_match('/iPad/i', $ua)) {
        $os = 'iPad (tablet)';
        $icon = '📲';
        $kind = 'tablet';
    } elseif (preg_match('/iPhone|iPod/i', $ua)) {
        $os = preg_match('/iPod/i', $ua) ? 'iPod' : 'iPhone';
        $icon = '📱';
        $kind = 'iphone';
    } elseif (preg_match('/Android/i', $ua)) {
        if (preg_match('/Mobile/i', $ua)) {
            $os = 'Android';
            $icon = '🤖';
            $kind = 'android';
        } else {
            $os = 'Android (tablet)';
            $icon = '📲';
            $kind = 'tablet';
        }
    } elseif (preg_match('/Windows NT 10\.0/i', $ua)) {
        $os = preg_match('/Windows 11|Build\/22\d{3}/i', $ua) ? 'Windows 11' : 'Windows 10';
        $icon = '🖥️';
    } elseif (preg_match('/Mac OS X|Macintosh/i', $ua)) {
        $os = 'macOS';
        $icon = '🖥️';
    } elseif (preg_match('/Linux/i', $ua)) {
        $os = 'Linux';
        $icon = '💻';
    }

    return [
        'os'      => $os,
        'browser' => device_parse_browser($ua),
        'icon'    => $icon,
        'kind'    => $kind,
    ];
}

function device_parse_browser(string $ua): string
{
    if (preg_match('/Edg\//i', $ua)) {
        return 'Edge';
    }
    if (preg_match('/Firefox\//i', $ua)) {
        return 'Firefox';
    }
    if (preg_match('/Chrome\//i', $ua) && !preg_match('/Edg/i', $ua)) {
        return 'Chrome';
    }
    if (preg_match('/Safari\//i', $ua) && !preg_match('/Chrome/i', $ua)) {
        return 'Safari';
    }
    return 'Desconhecido';
}

function device_ua_looks_bot(string $ua): bool
{
    $uaLower = strtolower($ua);
    foreach (['bot', 'crawl', 'spider', 'curl/', 'headless', 'python-requests'] as $hint) {
        if (str_contains($uaLower, $hint)) {
            return true;
        }
    }
    return false;
}

function device_reason_label(string $reason): string
{
    $labels = [
        'ua_empty'    => 'User-Agent vazio',
        'ua_bot'      => 'User-Agent de bot',
        'headers'     => 'Headers inválidos',
        'device'      => 'Dispositivo não permitido',
        'geo_unknown' => 'GeoIP indisponível',
        'hosting'     => 'Datacenter / VPS',
        'proxy_vpn'   => 'VPN ou proxy',
        'datacenter'  => 'Provedor cloud/VPS',
        'headless'    => 'Navegador headless',
    ];

    if (isset($labels[$reason])) {
        return $labels[$reason];
    }

    if (strncmp($reason, 'country_', 8) === 0) {
        return 'País bloqueado (' . strtoupper(substr($reason, 8)) . ')';
    }

    return $reason !== '' ? $reason : 'Bloqueado';
}

function device_kind_icon(string $kind): string
{
    $icons = [
        'iphone'  => '📱',
        'android' => '🤖',
        'tablet'  => '📲',
        'desktop' => '🖥️',
        'bot'     => '🤖',
    ];

    return $icons[$kind] ?? '💻';
}

function device_kind_label(string $kind): string
{
    $labels = [
        'iphone'  => 'iPhone / iOS',
        'android' => 'Android',
        'tablet'  => 'Tablet',
        'desktop' => 'Desktop',
    ];

    return $labels[$kind] ?? 'Outros';
}
