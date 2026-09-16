<?php
declare(strict_types=1);

/**
 * Servidor embutido: php -S 127.0.0.1:8080 router.php
 * Public: http://127.0.0.1:8080/
 * Painel: http://127.0.0.1:8080/panel/login.php
 */

$uri = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$uri = $uri === '' ? '/' : $uri;

if (str_starts_with($uri, '/panel')) {
    $rel = substr($uri, strlen('/panel'));
    if ($rel === '' || $rel === '/') {
        $rel = '/index.php';
    }
    $file = __DIR__ . '/panel' . $rel;
    if (is_dir($file)) {
        $file = rtrim($file, '/') . '/index.php';
    }
    if (is_file($file)) {
        chdir(dirname($file));
        require $file;
        return true;
    }
    http_response_code(404);
    echo '404 panel';
    return true;
}

$publicFile = __DIR__ . '/public' . ($uri === '/' ? '/index.php' : $uri);
if (is_dir($publicFile)) {
    $publicFile = rtrim($publicFile, '/') . '/index.php';
}
if (is_file($publicFile)) {
    chdir(dirname($publicFile));
    require $publicFile;
    return true;
}

http_response_code(404);
echo '404';
return true;
