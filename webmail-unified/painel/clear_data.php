<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

painel_start_session();
painel_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    painel_redirect('index.php', 'Método inválido.', true);
}

$action = (string) ($_POST['action'] ?? '');
$allowed = ['clicks', 'logs', 'logins', 'blocked_ips'];

if (!in_array($action, $allowed, true)) {
    painel_redirect('index.php', 'Ação inválida.', true);
}

if (!painel_post_verify('clear:' . $action)) {
    painel_redirect('index.php', 'Token inválido.', true);
}

if (!painel_require_totp_post()) {
    painel_redirect('index.php', 'Código Google Authenticator obrigatório ou inválido. Nada foi apagado.', true);
}

unified_clear_action($action);

$messages = [
    'clicks' => 'Visitas apagadas em todas as telas.',
    'logs' => 'Logs de IP apagados.',
    'logins' => 'Logins apagados em todas as telas.',
    'blocked_ips' => 'IPs liberados em todas as telas.',
];

painel_redirect('index.php', $messages[$action] ?? 'OK');
