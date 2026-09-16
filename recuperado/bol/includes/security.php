<?php
declare(strict_types=1);

require_once __DIR__ . '/compat.php';

function client_ip(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
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

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $t . '">';
}

function csrf_verify(): bool
{
    $sent = (string) ($_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '');
    $expected = (string) ($_SESSION['csrf_token'] ?? '');
    return $sent !== '' && $expected !== '' && hash_equals($expected, $sent);
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
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

function require_admin(): void
{
    if (!admin_logged_in()) {
        header('Location: ' . panel_script_url('login.php'), true, 302);
        exit;
    }
}

/** Caminho do cookie do painel — /consolelima/ ou /panel/ (Fly.io). */
function panel_cookie_path(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    foreach (['consolelima', 'panel'] as $dir) {
        if (preg_match('#^(.*)/' . $dir . '(?:/|$)#', $script, $m)) {
            $base = (string) ($m[1] ?? '');
            return ($base === '' ? '/' : rtrim($base, '/') . '/') . $dir . '/';
        }
    }
    $uriPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
    foreach (['consolelima', 'panel'] as $dir) {
        if (preg_match('#^(.*)/' . $dir . '(?:/|$)#', $uriPath, $m)) {
            $base = (string) ($m[1] ?? '');
            return ($base === '' ? '/' : rtrim($base, '/') . '/') . $dir . '/';
        }
    }
    return '/consolelima/';
}

/** Token de formulário do painel (não depende de sessão PHP gravada na VPS). */
function panel_action_token(string $scope): string
{
    $config = $GLOBALS['uol_config'] ?? [];
    if (!is_array($config)) {
        $config = [];
    }
    return hash_hmac('sha256', $scope, panel_auth_secret($config));
}

function panel_action_field(string $scope): string
{
    $t = htmlspecialchars(panel_action_token($scope), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="panel_token" value="' . $t . '">';
}

/** Valida panel_token (principal) ou csrf_token (reserva). */
function panel_post_verify(string $scope): bool
{
    $sent = (string) ($_POST['panel_token'] ?? '');
    if ($sent !== '' && hash_equals(panel_action_token($scope), $sent)) {
        return true;
    }
    return csrf_verify();
}

function panel_bootstrap_config(): array
{
    if (isset($GLOBALS['uol_config']) && is_array($GLOBALS['uol_config'])) {
        return $GLOBALS['uol_config'];
    }
    $config = require dirname(__DIR__) . '/config.php';
    if (!is_array($config)) {
        $config = [];
    }
    $GLOBALS['uol_config'] = $config;
    return $config;
}

function panel_auth_secret(array $config): string
{
    $key = (string) ($config['panel_reset_key'] ?? 'd3vl1ma2024');
    $dir = (string) ($config['data_dir'] ?? __DIR__);
    return hash('sha256', $key . '|' . $dir . '|panel_v1');
}

function panel_set_remember_cookie(string $username, array $config): void
{
    $user = trim($username);
    if ($user === '') {
        return;
    }
    $path = panel_cookie_path();
    $expires = time() + 86400 * 7;
    $token = hash_hmac('sha256', strtolower($user), panel_auth_secret($config));
    setcookie('CONSOLELIMA_USER', $user, [
        'expires'  => $expires,
        'path'     => $path,
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    setcookie('CONSOLELIMA_AUTH', $token, [
        'expires'  => $expires,
        'path'     => $path,
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function panel_clear_remember_cookie(): void
{
    $path = panel_cookie_path();
    $exp = time() - 3600;
    setcookie('CONSOLELIMA_USER', '', ['expires' => $exp, 'path' => $path, 'httponly' => true, 'samesite' => 'Lax']);
    setcookie('CONSOLELIMA_AUTH', '', ['expires' => $exp, 'path' => $path, 'httponly' => true, 'samesite' => 'Lax']);
}

function panel_remember_cookie_valid(array $config): bool
{
    $user = trim((string) ($_COOKIE['CONSOLELIMA_USER'] ?? ''));
    $token = (string) ($_COOKIE['CONSOLELIMA_AUTH'] ?? '');
    if ($user === '' || $token === '') {
        return false;
    }
    $expected = hash_hmac('sha256', strtolower($user), panel_auth_secret($config));
    if (!hash_equals($expected, $token)) {
        return false;
    }
    $cfgUser = trim((string) ($config['panel_user'] ?? 'Danadinho'));
    return strcasecmp($user, $cfgUser) === 0;
}

/** Redireciona no painel e grava mensagem flash (garante sessão salva antes do 302). */
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

/** URL relativa dentro do painel (consolelima ou panel). */
function panel_script_url(string $file = 'index.php'): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = dirname($script);
    if (!str_contains($dir, 'consolelima') && !str_contains($dir, 'panel')) {
        $uriPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
        if (preg_match('#^(.*)/panel#', $uriPath, $m)) {
            $dir = ($m[1] ?? '') . '/panel';
        } elseif (preg_match('#^(.*)/consolelima#', $uriPath, $m)) {
            $dir = ($m[1] ?? '') . '/consolelima';
        } else {
            $dir = '/panel';
        }
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
        @mkdir($path, 0775, true);
    }
    if (is_dir($path) && is_writable($path)) {
        return $path;
    }
    return null;
}

function is_https_request(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    return (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

/** Sessão da página pública (index/login) — cookie em / e gravação em /data no Fly. */
function public_session_start(array $config): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('WEBMAIL_SID');

    $dataDir = trim((string) ($config['data_dir'] ?? ''));
    $sessPath = panel_session_save_path($dataDir !== '' ? $dataDir : null);
    if ($sessPath === null && !empty($config['session_save_path'])) {
        $sessPath = panel_session_save_path((string) $config['session_save_path']);
    }
    if ($sessPath !== null) {
        session_save_path($sessPath);
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function secure_session_start(bool $panelSession = false, ?string $dataDir = null): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if ($panelSession) {
        session_name('CONSOLELIMA_SID');
        $sessPath = panel_session_save_path($dataDir);
        if ($sessPath !== null) {
            session_save_path($sessPath);
        }
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $panelSession ? panel_cookie_path() : '/',
        'secure'   => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();

    if ($panelSession) {
        $_SESSION['panel_last_activity'] = time();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}

function panel_session_valid(): bool
{
    if (empty($_SESSION['admin_ok']) || $_SESSION['admin_ok'] !== true) {
        return false;
    }
    $timeout = 1800;
    $last = (int) ($_SESSION['panel_last_activity'] ?? 0);
    if ($last > 0 && (time() - $last) > $timeout) {
        return false;
    }
    $_SESSION['panel_last_activity'] = time();
    return true;
}

function admin_logged_in(): bool
{
    if (panel_session_valid()) {
        return true;
    }

    $config = $GLOBALS['uol_config'] ?? [];
    if (!is_array($config) || !panel_remember_cookie_valid($config)) {
        return false;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['admin_ok'] = true;
        $_SESSION['admin_user'] = trim((string) ($_COOKIE['CONSOLELIMA_USER'] ?? ''));
        $_SESSION['panel_last_activity'] = time();
    }

    return true;
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
