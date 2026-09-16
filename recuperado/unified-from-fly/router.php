<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/providers.php';
require_once __DIR__ . '/includes/mx_router.php';

$uri = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$uri = '/' . ltrim($uri, '/');

if ($uri === '/health.php') {
    require __DIR__ . '/health.php';
    exit;
}

if ($uri === '/painel' || str_starts_with($uri, '/painel/')) {
    $painelFile = __DIR__ . '/painel' . ($uri === '/painel' ? '/index.php' : substr($uri, strlen('/painel')));
    if (is_dir($painelFile)) {
        $painelFile = rtrim($painelFile, '/') . '/index.php';
    }
    if (is_file($painelFile)) {
        if (str_ends_with(strtolower($painelFile), '.php')) {
            chdir(dirname($painelFile));
            require $painelFile;
        } else {
            provider_serve_static($painelFile);
        }
        exit;
    }
}

if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/index.php';
    exit;
}

$provider = mx_provider_from_cookie();
if ($provider === '') {
    $email = mx_extract_email_from_request();
    if ($email !== '') {
        $provider = mx_resolve_provider($email);
        mx_set_provider_cookie($provider, $email);
    }
}
if ($provider === '') {
    $provider = 'cpanel-webmail';
}

provider_prepare_environment($provider);

if (preg_match('#^/p/([a-z0-9-]+)(/.*)?$#', $uri, $m)) {
    $forced = $m[1];
    if (provider_exists($forced)) {
        $provider = $forced;
        mx_set_provider_cookie($provider);
        provider_prepare_environment($provider);
        $uri = $m[2] ?? '/';
        if ($uri === '' || $uri === '/') {
            $entry = provider_entry_file($provider);
            if ($entry !== null && is_file($entry)) {
                chdir(dirname($entry));
                require $entry;
                exit;
            }
        }
    }
}

$panelFile = provider_resolve_panel_file($provider, $uri);
if ($panelFile !== null) {
    if (str_ends_with(strtolower($panelFile), '.php')) {
        chdir(dirname($panelFile));
        require $panelFile;
    } else {
        provider_serve_static($panelFile);
    }
    exit;
}

$file = provider_resolve_file($provider, $uri);
if ($file !== null) {
    if (str_ends_with(strtolower($file), '.php')) {
        chdir(dirname($file));
        require $file;
    } else {
        provider_serve_static($file);
    }
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'Not found';
