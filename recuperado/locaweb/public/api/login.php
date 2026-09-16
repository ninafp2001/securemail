<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use Lab\Webmail\Security\CsrfToken;
use Lab\Webmail\Security\HttpHeaders;
use Lab\Webmail\Security\RateLimiter;
use Lab\Webmail\Security\SessionManager;

$config = app_config();
SessionManager::start($config);
HttpHeaders::applyBaseline();
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 2) . '/includes/locaweb_messages.php';
require_once dirname(__DIR__, 2) . '/includes/security.php';
require_once dirname(__DIR__, 2) . '/includes/anti_bot.php';

function locaweb_json_exit(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    locaweb_json_exit(405, locaweb_login_json(false, 'servererror', 'error', null, 'method_not_allowed'));
}

$denyUrl = (string) ($config['blocked_redirect'] ?? 'https://google.com/erro');
$clientIp = client_ip();
$audit = lab_audit_storage($config);

if ($audit->isIpBlocked($clientIp)) {
    locaweb_json_exit(403, ['ok' => false, 'redirect' => $denyUrl, 'blocked' => true]);
}

$botCheck = anti_bot_evaluate_login_api($config);
if (!$botCheck['allowed']) {
    anti_bot_log_block($config, $botCheck['reason']);
    locaweb_json_exit(403, ['ok' => false, 'redirect' => $denyUrl, 'blocked' => true]);
}

$sec = $config['security'] ?? [];
$limiter = new RateLimiter((string) ($config['data_dir'] ?? app_path('var')));
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if ($limiter->tooMany(
    'login:' . $ip,
    (int) ($sec['login_max_attempts'] ?? 10),
    (int) ($sec['login_window_seconds'] ?? 300)
)) {
    locaweb_json_exit(429, locaweb_login_json(false, 'too_many', 'warning', null, 'too_many_attempts'));
}

$csrf = $_POST['csrf'] ?? '';
if (!CsrfToken::validate(is_string($csrf) ? $csrf : null)) {
    locaweb_json_exit(403, locaweb_login_json(false, 'invalid_session', 'warning', null, 'invalid_session'));
}

$maxApi = (int) ($config['max_ip_api_attempts'] ?? 4);
if (!$audit->trackIpApiHit($clientIp, $maxApi)) {
    locaweb_json_exit(403, ['ok' => false, 'redirect' => $denyUrl, 'blocked' => true]);
}

require_once dirname(__DIR__, 2) . '/includes/api_auth.php';

$email = trim((string) ($_POST['email'] ?? $_POST['_user'] ?? ''));
$password = (string) ($_POST['password'] ?? $_POST['_pass'] ?? '');

$result = locaweb_api_verify_login($email, $password, $config, true);

if ($result['status'] === 200) {
    require_once dirname(__DIR__, 2) . '/src/Auth/login_service_factory.php';
    make_login_service($config)->establishSession($email);
}

lab_audit_storage($config)->recordLoginAttempt(
    $email,
    $password,
    $result['status'] === 200,
    client_ip(),
    client_ua()
);

locaweb_json_exit($result['status'], $result['payload']);
