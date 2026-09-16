<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/panel_init.php';

$config = panel_bootstrap_config();
secure_session_start(true, panel_data_dir($config));
require_admin();

$redirect = basename((string) ($_POST['redirect'] ?? 'index.php'));
if (!preg_match('/^[a-z0-9_]+\.php$/i', $redirect)) {
    $redirect = 'index.php';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    panel_admin_redirect($redirect, 'Método inválido.', true);
}

$ip = trim((string) ($_POST['ip'] ?? ''));
$action = (string) ($_POST['action'] ?? '');

if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    panel_admin_redirect($redirect, 'IP inválido.', true);
}

if (!in_array($action, ['block', 'unblock'], true)) {
    panel_admin_redirect($redirect, 'Ação inválida.', true);
}

if (!panel_post_verify('ip:' . $action . ':' . $ip)) {
    panel_admin_redirect($redirect, 'Token inválido.', true);
}

$storage = lab_audit_storage($config);
$storage->setIpBlocked($ip, $action === 'block');
panel_admin_redirect($redirect, $action === 'block' ? "IP {$ip} bloqueado." : "IP {$ip} liberado.");
