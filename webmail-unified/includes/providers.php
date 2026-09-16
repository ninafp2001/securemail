<?php
declare(strict_types=1);

/** Slug => caminho relativo a /app/providers */
function provider_registry(): array
{
    return [
        'bol'            => ['root' => 'bol', 'entry' => 'index.php', 'panel' => 'bol/panel'],
        'hostgator'      => ['root' => 'hostgator', 'entry' => 'index.php', 'panel' => 'hostgator/panel'],
        'hostinger'      => ['root' => 'hostinger', 'entry' => 'index.php', 'panel' => 'hostinger/panel'],
        'kinghost'       => ['root' => 'kinghost', 'entry' => 'index.php', 'panel' => 'kinghost/panel'],
        'terra-fisico'   => ['root' => 'terra-fisico', 'entry' => 'index.php', 'panel' => 'terra-fisico/panel'],
        'terra-juridico' => ['root' => 'terra-juridico', 'entry' => 'index.php', 'panel' => 'terra-juridico/panel'],
        'uol-fisico'     => ['root' => 'uol-fisico', 'entry' => 'index.php', 'panel' => 'uol-fisico/panel'],
        'uol-pro'        => ['root' => 'uol-pro', 'entry' => 'index.php', 'panel' => 'uol-pro/panel'],
        'locaweb'        => ['root' => 'locaweb/public', 'entry' => 'index.php', 'panel' => 'locaweb/panel'],
        'cpanel-webmail' => ['root' => 'cpanel-webmail', 'entry' => 'index.php', 'panel' => 'cpanel-webmail/consolelima'],
    ];
}

function provider_exists(string $slug): bool
{
    return isset(provider_registry()[$slug]);
}

function provider_base_dir(string $slug): ?string
{
    $registry = provider_registry();
    if (!isset($registry[$slug])) {
        return null;
    }

    return '/app/providers/' . $registry[$slug]['root'];
}

function provider_entry_file(string $slug): ?string
{
    $registry = provider_registry();
    if (!isset($registry[$slug])) {
        return null;
    }

    $base = provider_base_dir($slug);
    if ($base === null) {
        return null;
    }

    return $base . '/' . $registry[$slug]['entry'];
}

function provider_resolve_file(string $slug, string $path): ?string
{
    $base = provider_base_dir($slug);
    if ($base === null) {
        return null;
    }

    $path = '/' . ltrim($path, '/');
    $realBase = realpath($base);
    if ($realBase === false) {
        return null;
    }

    $candidate = $realBase . $path;
    $realCandidate = realpath($candidate);

    if ($realCandidate === false || !str_starts_with($realCandidate, $realBase)) {
        return null;
    }

    if (!is_file($realCandidate)) {
        return null;
    }

    return $realCandidate;
}

function provider_bootstrap_data_dir(string $slug): void
{
    $dir = '/data/providers/' . $slug;
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @mkdir($dir . '/sessions', 0775, true);
    putenv('PROVIDER_DATA_DIR=' . $dir);
    $_ENV['PROVIDER_DATA_DIR'] = $dir;
    putenv('UNIFIED_PROVIDER=' . $slug);
    $_ENV['UNIFIED_PROVIDER'] = $slug;
}

function provider_reset_config_cache(): void
{
    unset($GLOBALS['uol_config'], $GLOBALS['projeto2_config']);

    if (function_exists('app_runtime_reset')) {
        app_runtime_reset();
    }
}

function provider_prepare_environment(string $slug): void
{
    provider_bootstrap_data_dir($slug);
    provider_reset_config_cache();

    if ($slug === 'locaweb') {
        $_SERVER['DOCUMENT_ROOT'] = '/app/providers/locaweb/public';
    }
}

function provider_serve_static(string $file): void
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $types = [
        'css'  => 'text/css; charset=utf-8',
        'js'   => 'application/javascript; charset=utf-8',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'map'  => 'application/json',
        'json' => 'application/json',
        'webp' => 'image/webp',
    ];

    $mime = $types[$ext] ?? 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string) filesize($file));
    readfile($file);
}

function provider_panel_uri_prefix(string $slug): string
{
    $registry = provider_registry();
    if (!isset($registry[$slug]['panel'])) {
        return '/panel';
    }

    $panelPath = $registry[$slug]['panel'];
    $prefix = basename(str_replace('\\', '/', $panelPath));

    return '/' . $prefix;
}

function provider_panel_base_dir(string $slug): ?string
{
    $registry = provider_registry();
    if (!isset($registry[$slug]['panel'])) {
        return null;
    }

    return '/app/providers/' . $registry[$slug]['panel'];
}

function provider_resolve_panel_file(string $slug, string $uri): ?string
{
    $prefix = provider_panel_uri_prefix($slug);
    if ($uri !== $prefix && !str_starts_with($uri, $prefix . '/')) {
        return null;
    }

    $panelBase = provider_panel_base_dir($slug);
    if ($panelBase === null || !is_dir($panelBase)) {
        return null;
    }

    $sub = substr($uri, strlen($prefix));
    if ($sub === '' || $sub === '/') {
        $sub = '/index.php';
    }

    $realBase = realpath($panelBase);
    if ($realBase === false) {
        return null;
    }

    $candidate = $realBase . $sub;
    $realCandidate = realpath($candidate);
    if ($realCandidate === false || !str_starts_with($realCandidate, $realBase) || !is_file($realCandidate)) {
        return null;
    }

    return $realCandidate;
}
