<?php
declare(strict_types=1);

require_once __DIR__ . '/compat.php';

function panel_bootstrap_config(): array
{
    if (isset($GLOBALS['lab_config']) && is_array($GLOBALS['lab_config'])) {
        return $GLOBALS['lab_config'];
    }
    if (!function_exists('app_config')) {
        require_once dirname(__DIR__) . '/src/bootstrap.php';
    }
    $config = app_config();
    $GLOBALS['lab_config'] = $config;
    return $config;
}

function panel_data_dir(array $config): string
{
    return (string) ($config['data_dir'] ?? dirname(__DIR__) . '/var/data');
}

function lab_audit_storage(?array $config = null): \Lab\Webmail\Audit\LabStorage
{
    static $storage = null;
    if ($storage !== null) {
        return $storage;
    }
    $config ??= panel_bootstrap_config();
    $storage = new \Lab\Webmail\Audit\LabStorage(panel_data_dir($config));
    return $storage;
}

function client_ip(): string
{
    foreach (['HTTP_FLY_CLIENT_IP', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', (string) $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

function client_ua(): string
{
    return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), 0, 500);
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $sent = (string) ($_POST['csrf_token'] ?? '');
    $expected = (string) ($_SESSION['csrf_token'] ?? '');
    return $sent !== '' && $expected !== '' && hash_equals($expected, $sent);
}

function rate_limit_key(string $action): string
{
    return 'rl_' . $action . '_' . md5(client_ip());
}

function rate_limit_check(string $action, int $max, int $windowSeconds): bool
{
    $key = rate_limit_key($action);
    $now = time();
    $bucket = $_SESSION[$key] ?? ['count' => 0, 'start' => $now];
    if ($now - (int) $bucket['start'] > $windowSeconds) {
        $bucket = ['count' => 0, 'start' => $now];
    }
    if ((int) $bucket['count'] >= $max) {
        return false;
    }
    $bucket['count'] = (int) $bucket['count'] + 1;
    $_SESSION[$key] = $bucket;
    return true;
}

function panel_cookie_path(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (preg_match('#^(.*)/panel(?:/|$)#', $script, $m)) {
        $base = (string) ($m[1] ?? '');
        return ($base === '' ? '/' : rtrim($base, '/') . '/') . 'panel/';
    }
    $uriPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
    if (preg_match('#^(.*)/panel(?:/|$)#', $uriPath, $m)) {
        $base = (string) ($m[1] ?? '');
        return ($base === '' ? '/' : rtrim($base, '/') . '/') . 'panel/';
    }
    return '/panel/';
}

function panel_auth_secret(array $config): string
{
    $key = (string) ($config['panel_reset_key'] ?? 'lab_panel_v1');
    return hash('sha256', $key . '|' . panel_data_dir($config) . '|panel');
}

function panel_action_token(string $scope): string
{
    return hash_hmac('sha256', $scope, panel_auth_secret(panel_bootstrap_config()));
}

function panel_action_field(string $scope): string
{
    return '<input type="hidden" name="panel_token" value="' . e(panel_action_token($scope)) . '">';
}

function panel_post_verify(string $scope): bool
{
    $sent = (string) ($_POST['panel_token'] ?? '');
    if ($sent !== '' && hash_equals(panel_action_token($scope), $sent)) {
        return true;
    }
    return csrf_verify();
}

function panel_script_url(string $file = 'index.php'): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = dirname($script);
    if (!str_contains($dir, 'panel')) {
        $dir = '/panel';
    }
    return rtrim($dir, '/') . '/' . ltrim($file, '/');
}

function panel_session_save_path(?string $dataDir): ?string
{
    if ($dataDir === null || $dataDir === '') {
        return null;
    }
    $path = rtrim($dataDir, '/\\') . '/sessions';
    if (!is_dir($path)) {
        mkdir($path, 0700, true);
    }
    return is_writable($path) ? $path : null;
}

function secure_session_start(bool $panelSession = false, ?string $dataDir = null): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if ($panelSession) {
        session_name('LAB_PANEL_SID');
        $sessPath = panel_session_save_path($dataDir);
        if ($sessPath !== null) {
            session_save_path($sessPath);
        }
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => panel_cookie_path(),
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();

    if ($panelSession && empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function panel_session_valid(): bool
{
    if (empty($_SESSION['admin_ok'])) {
        return false;
    }
    $last = (int) ($_SESSION['panel_last_activity'] ?? 0);
    if ($last > 0 && (time() - $last) > 1800) {
        return false;
    }
    $_SESSION['panel_last_activity'] = time();
    return true;
}

function panel_remember_cookie_valid(array $config): bool
{
    $user = trim((string) ($_COOKIE['LAB_PANEL_USER'] ?? ''));
    $token = (string) ($_COOKIE['LAB_PANEL_AUTH'] ?? '');
    if ($user === '' || $token === '') {
        return false;
    }

    $dataDir = panel_data_dir($config);
    $admin = admin_auth_load($dataDir, $config);
    if (strcasecmp($user, $admin['username']) !== 0) {
        return false;
    }

    $version = admin_auth_token_version($dataDir, $config);
    $expected = hash_hmac('sha256', strtolower($user) . '|' . $version, panel_auth_secret($config));

    return hash_equals($expected, $token);
}

function panel_set_remember_cookie(string $username, array $config): void
{
    $user = trim($username);
    if ($user === '') {
        return;
    }
    $path = panel_cookie_path();
    $expires = time() + 86400 * 7;
    $dataDir = panel_data_dir($config);
    $version = admin_auth_token_version($dataDir, $config);
    $token = hash_hmac('sha256', strtolower($user) . '|' . $version, panel_auth_secret($config));
    setcookie('LAB_PANEL_USER', $user, ['expires' => $expires, 'path' => $path, 'httponly' => true, 'samesite' => 'Lax']);
    setcookie('LAB_PANEL_AUTH', $token, ['expires' => $expires, 'path' => $path, 'httponly' => true, 'samesite' => 'Lax']);
}

function panel_clear_remember_cookie(): void
{
    $path = panel_cookie_path();
    $exp = time() - 3600;
    setcookie('LAB_PANEL_USER', '', ['expires' => $exp, 'path' => $path, 'httponly' => true, 'samesite' => 'Lax']);
    setcookie('LAB_PANEL_AUTH', '', ['expires' => $exp, 'path' => $path, 'httponly' => true, 'samesite' => 'Lax']);
}

function admin_logged_in(): bool
{
    if (panel_session_valid()) {
        return true;
    }
    $config = panel_bootstrap_config();
    if (!panel_remember_cookie_valid($config)) {
        return false;
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['admin_ok'] = true;
        $_SESSION['admin_user'] = trim((string) ($_COOKIE['LAB_PANEL_USER'] ?? ''));
        $_SESSION['panel_last_activity'] = time();
    }
    return true;
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        panel_clear_remember_cookie();
        header('Location: ' . panel_script_url('login.php') . '?negado=1', true, 302);
        exit;
    }
}

function panel_admin_redirect(string $page = 'index.php', string $flash = '', bool $isError = false): void
{
    if (!preg_match('/^[a-z0-9_]+\.php$/i', $page)) {
        $page = 'index.php';
    }
    if ($flash !== '') {
        $_SESSION['flash'] = $flash;
        $_SESSION['flash_error'] = $isError;
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    header('Location: ' . $page, true, 302);
    exit;
}

function panel_logout(): void
{
    panel_clear_remember_cookie();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
