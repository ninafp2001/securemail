<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

painel_start_session();
painel_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método inválido']);
    exit;
}

if (!painel_post_verify('console:view')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token inválido']);
    exit;
}

if (!painel_require_totp_post()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Código Google Authenticator inválido']);
    exit;
}

try {
    $entries = unified_console_entries();
    echo json_encode(['ok' => true, 'entries' => $entries], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erro ao carregar logins']);
}
