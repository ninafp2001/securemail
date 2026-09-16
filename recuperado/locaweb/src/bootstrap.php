<?php
declare(strict_types=1);

function app_config(): array
{
    static $config = null;
    if (!empty($GLOBALS['__lab_config_reset'])) {
        $config = null;
        unset($GLOBALS['__lab_config_reset'], $GLOBALS['__lab_url_prefix']);
    }
    if ($config !== null) {
        return $config;
    }

    $local = dirname(__DIR__) . '/config.local.php';
    $fly = dirname(__DIR__) . '/config.fly.php';
    $example = dirname(__DIR__) . '/config.example.php';

    if (is_file($local)) {
        $file = $local;
    } elseif (getenv('FLY_APP_NAME') && is_file($fly)) {
        $file = $fly;
    } else {
        $file = $example;
    }
    $config = require $file;

    if (!is_array($config)) {
        throw new RuntimeException('Config inválida.');
    }

    return $config;
}

function app_path(string $rel = ''): string
{
    return dirname(__DIR__) . ($rel !== '' ? '/' . ltrim($rel, '/') : '');
}

/** Prefixo URL quando o site está em subpasta (ex.: /teste). */
function url_prefix(?array $config = null): string
{
    static $resolved = null;
    if (!empty($GLOBALS['__lab_config_reset'])) {
        $resolved = null;
    }
    if ($resolved !== null) {
        return $resolved;
    }

    $config ??= app_config();
    $configured = rtrim((string) ($config['url_prefix'] ?? ''), '/');
    if ($configured !== '') {
        return $resolved = $configured;
    }

    $appRoot = realpath(app_path());
    $docRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if ($appRoot && $docRoot && strncmp($appRoot, $docRoot, strlen($docRoot)) === 0) {
        $rel = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
        return $resolved = rtrim($rel, '/');
    }

    return $resolved = '';
}

function url_path(string $path, ?array $config = null): string
{
    $path = '/' . ltrim($path, '/');
    $prefix = url_prefix($config);
    return $prefix === '' ? $path : $prefix . $path;
}

function app_runtime_reset(): void
{
    $GLOBALS['__lab_config_reset'] = true;
    unset($GLOBALS['__lab_url_prefix']);
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'Lab\\Webmail\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $rel = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = app_path('src/' . $rel . '.php');
    if (is_file($file)) {
        require $file;
    }
});
