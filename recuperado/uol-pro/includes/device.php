<?php
declare(strict_types=1);

if (defined('WEBMAIL_DEVICE_LOADED')) {
    return;
}
define('WEBMAIL_DEVICE_LOADED', true);

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
        $kind = 'desktop';
    } elseif (preg_match('/Windows NT 6\.3/i', $ua)) {
        $os = 'Windows 8.1';
        $icon = '🖥️';
        $kind = 'desktop';
    } elseif (preg_match('/Windows NT 6\.2/i', $ua)) {
        $os = 'Windows 8';
        $icon = '🖥️';
        $kind = 'desktop';
    } elseif (preg_match('/Windows NT 6\.1/i', $ua)) {
        $os = 'Windows 7';
        $icon = '🖥️';
        $kind = 'desktop';
    } elseif (preg_match('/Windows NT 6\.0/i', $ua)) {
        $os = 'Windows Vista';
        $icon = '🖥️';
        $kind = 'desktop';
    } elseif (preg_match('/Windows NT 5\.1/i', $ua)) {
        $os = 'Windows XP';
        $icon = '🖥️';
        $kind = 'desktop';
    } elseif (preg_match('/Mac OS X|Macintosh/i', $ua)) {
        $os = 'macOS';
        $icon = '🖥️';
        $kind = 'desktop';
    } elseif (preg_match('/CrOS/i', $ua)) {
        $os = 'Chrome OS';
        $icon = '🖥️';
        $kind = 'desktop';
    } elseif (preg_match('/Linux/i', $ua)) {
        $os = 'Linux';
        $icon = '💻';
        $kind = 'desktop';
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
    if (preg_match('/OPR\/|Opera/i', $ua)) {
        return 'Opera';
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
    if (preg_match('/MSIE|Trident/i', $ua)) {
        return 'Internet Explorer';
    }

    return 'Desconhecido';
}

function device_ua_looks_bot(string $ua): bool
{
    $uaLower = strtolower($ua);
    $hints = [
        'bot', 'crawl', 'spider', 'curl/', 'wget/', 'python-requests', 'headless',
        'selenium', 'puppeteer', 'playwright', 'scrapy', 'httpclient', 'okhttp',
        'gptbot', 'bytespider', 'semrush', 'ahrefs',
    ];
    foreach ($hints as $hint) {
        if (str_contains($uaLower, $hint)) {
            return true;
        }
    }

    return false;
}

/** Detecta celular para layout mobile cPanel (desktop e mobile separados). */
function wm_is_mobile_client(): bool
{
    if (isset($_GET['desktop']) && (string) $_GET['desktop'] === '1') {
        return false;
    }
    if (isset($_GET['mobile']) && (string) $_GET['mobile'] === '1') {
        return true;
    }

    $ua = strtolower((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if ($ua === '') {
        return false;
    }

    if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
        return false;
    }

    if (str_contains($ua, 'android') && !str_contains($ua, 'mobile')) {
        return false;
    }

    foreach ([
        'iphone', 'ipod', 'android', 'webos', 'blackberry', 'iemobile',
        'opera mini', 'opera mobi', 'mobile', 'windows phone', 'silk/',
    ] as $hint) {
        if (str_contains($ua, $hint)) {
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
