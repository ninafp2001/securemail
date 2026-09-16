<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/panel_init.php';

$config = panel_bootstrap_config();
$dataDir = panel_data_dir($config);
secure_session_start(true, $dataDir);
require_admin();

$redirect = basename((string) ($_POST['redirect'] ?? 'index.php'));
if (!preg_match('/^[a-z0-9_]+\.php$/i', $redirect)) {
    $redirect = 'index.php';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    panel_admin_redirect($redirect, 'Método inválido.', true);
}

$action = (string) ($_POST['action'] ?? '');
$allowed = ['clicks', 'logs', 'logins', 'attempts', 'blocked_ips'];
if (!in_array($action, $allowed, true)) {
    panel_admin_redirect($redirect, 'Ação inválida.', true);
}

if (!panel_post_verify('clear:' . $action)) {
    panel_admin_redirect($redirect, 'Token inválido. Atualize (F5).', true);
}

$storage = panel_storage($config);
$ok = false;
$flash = 'Erro.';

switch ($action) {
    case 'clicks':
        $ok = $storage->clearClicks();
        $flash = $ok ? 'Visitas apagadas.' : 'Erro ao apagar visitas.';
        break;
    case 'logs':
        $ok = $storage->clearLogs();
        $flash = $ok ? 'IPs e bots apagados.' : 'Erro ao apagar IPs.';
        break;
    case 'logins':
        $ok = $storage->clearLogins();
        $flash = $ok ? 'Logins apagados.' : 'Erro ao apagar logins.';
        break;
    case 'attempts':
        $ok = $storage->clearLogins();
        $flash = $ok ? 'Tentativas e logins apagados.' : 'Erro ao apagar tentativas.';
        break;
    case 'blocked_ips':
        $count = $storage->countBlockedIps();
        $ok = $storage->clearAllBlockedIps();
        $flash = $ok
            ? ($count > 0 ? "Todos os {$count} IP(s) bloqueados foram liberados." : 'Nenhum IP bloqueado — contadores zerados.')
            : 'Erro ao liberar IPs bloqueados.';
        break;
}

panel_admin_redirect($redirect, $flash, !$ok);
