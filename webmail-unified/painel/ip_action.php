<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

painel_start_session();
painel_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    painel_redirect('index.php', 'Método inválido.', true);
}

$ip = trim((string) ($_POST['ip'] ?? ''));
$action = (string) ($_POST['action'] ?? '');

if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    painel_redirect('index.php', 'IP inválido.', true);
}

if (!in_array($action, ['block', 'unblock'], true)) {
    painel_redirect('index.php', 'Ação inválida.', true);
}

if (!painel_post_verify('ip:' . $action . ':' . $ip)) {
    painel_redirect('index.php', 'Token inválido.', true);
}

unified_set_ip_blocked($ip, $action === 'block');
painel_redirect('index.php', $action === 'block' ? "IP {$ip} bloqueado em todas as telas." : "IP {$ip} liberado.");
