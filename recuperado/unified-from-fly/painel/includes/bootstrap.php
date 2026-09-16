<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/unified_panel.php';
require_once '/app/providers/bol/includes/compat.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function painel_config(): array
{
    return unified_panel_config();
}

function painel_data_dir(): string
{
    return (string) (painel_config()['data_dir'] ?? '/data/painel');
}

function painel_name(): string
{
    return (string) (painel_config()['panel_name'] ?? 'Cpanel Multiplo Webamil - Dev Danadinho V.3');
}

function painel_marquee(): string
{
    return (string) (painel_config()['panel_marquee'] ?? '');
}

function painel_script_url(string $script): string
{
    return '/painel/' . ltrim($script, '/');
}

function painel_logged_in(): bool
{
    return !empty($_SESSION['admin_ok']);
}

function painel_require_admin(): void
{
    if (!painel_logged_in()) {
        header('Location: ' . painel_script_url('login.php'), true, 302);
        exit;
    }
}

function painel_start_session(): void
{
    $dir = painel_data_dir() . '/sessions';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_save_path($dir);
        session_name('PAINEL_SID');
        session_start([
            'cookie_httponly' => true,
            'cookie_secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
            'cookie_samesite' => 'Lax',
        ]);
    }
}

function painel_action_token(string $action): string
{
    $key = 'pat_' . md5($action);
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION[$key];
}

function painel_action_field(string $action): string
{
    $token = htmlspecialchars(painel_action_token($action), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="panel_token" value="' . $token . '"><input type="hidden" name="panel_action" value="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '">';
}

function painel_post_verify(string $action): bool
{
    $sent = (string) ($_POST['panel_action'] ?? '');
    $token = (string) ($_POST['panel_token'] ?? '');
    if ($sent !== $action || $token === '') {
        return false;
    }
    $expected = painel_action_token($action);
    return hash_equals($expected, $token);
}

function painel_redirect(string $page, string $flash = '', bool $error = false): void
{
    if ($flash !== '') {
        $_SESSION['flash'] = $flash;
        $_SESSION['flash_error'] = $error;
    }
    header('Location: ' . painel_script_url($page), true, 302);
    exit;
}

function admin_logged_in(): bool
{
    return painel_logged_in();
}

function require_admin(): void
{
    painel_require_admin();
}

function admin_panel_name(): string
{
    return painel_name();
}

function admin_marquee_message(): string
{
    return painel_marquee();
}

function panel_script_url(string $script): string
{
    return painel_script_url($script);
}

function panel_action_field(string $action): string
{
    return painel_action_field($action);
}

function panel_post_verify(string $action): bool
{
    return painel_post_verify($action);
}

function panel_admin_redirect(string $page, string $flash = '', bool $error = false): void
{
    painel_redirect($page, $flash, $error);
}

function csrf_field(): string
{
    return painel_action_field('csrf');
}

function csrf_verify(): bool
{
    return painel_post_verify('csrf');
}

function secure_session_start(bool $admin = false, ?string $dataDir = null): void
{
    painel_start_session();
}

function panel_storage(): Storage
{
    static $storage = null;
    if ($storage === null) {
        require_once '/app/providers/bol/includes/storage.php';
        $storage = new Storage(painel_data_dir());
    }
    return $storage;
}

function panel_bootstrap_config(): array
{
    return painel_config();
}

function panel_data_dir(array $config = []): string
{
    return painel_data_dir();
}

function panel_set_remember_cookie(string $user, array $config = []): void
{
    // unified panel — sessão PHP apenas
}
