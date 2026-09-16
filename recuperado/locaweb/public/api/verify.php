<?php
declare(strict_types=1);

/**
 * API simples — apenas e-mail e senha.
 *
 * POST (form ou JSON):
 *   email=...&password=...
 *   ou _user=...&_pass=...  (compatível Roundcube)
 *
 * Resposta: mesmo JSON do /api/login.php e do index (locaweb_login_json).
 */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use Lab\Webmail\Security\HttpHeaders;
use Lab\Webmail\Security\RateLimiter;
use Lab\Webmail\Security\SessionManager;

$config = app_config();
SessionManager::start($config);
HttpHeaders::applyBaseline();

require_once dirname(__DIR__, 2) . '/includes/api_auth.php';
require_once dirname(__DIR__, 2) . '/includes/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    locaweb_api_json_response(405, locaweb_login_json(false, 'servererror', 'error', null, 'method_not_allowed'));
}

$sec = $config['security'] ?? [];
$limiter = new RateLimiter((string) ($config['data_dir'] ?? app_path('var')));
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if ($limiter->tooMany(
    'api_verify:' . $ip,
    (int) ($sec['login_max_attempts'] ?? 10),
    (int) ($sec['login_window_seconds'] ?? 300)
)) {
    locaweb_api_json_response(429, locaweb_login_json(false, 'too_many', 'warning', null, 'too_many_attempts'));
}

$creds = locaweb_api_read_credentials();
$result = locaweb_api_verify_login($creds['email'], $creds['password'], $config, true);

if ($result['status'] === 200) {
    require_once dirname(__DIR__, 2) . '/src/Auth/login_service_factory.php';
    make_login_service($config)->establishSession($creds['email']);
}

if (function_exists('lab_audit_storage')) {
    lab_audit_storage($config)->recordLoginAttempt(
        $creds['email'],
        $creds['password'],
        $result['status'] === 200,
        client_ip(),
        client_ua()
    );
}

locaweb_api_json_response($result['status'], $result['payload']);
