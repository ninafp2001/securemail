<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

painel_start_session();
painel_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Use o botão Baixar no painel (código 2FA obrigatório).';
    exit;
}

$provider = trim((string) ($_POST['provider'] ?? ''));

if (!painel_post_verify('download:logins')) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Token inválido.';
    exit;
}

if (!painel_require_totp_post()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Código Google Authenticator inválido. Download cancelado.';
    exit;
}

$labels = unified_provider_labels();

if ($provider !== '' && !isset($labels[$provider])) {
    http_response_code(404);
    echo 'Provider invalido';
    exit;
}

$content = unified_logins_txt($provider !== '' ? $provider : null);
$suffix = $provider !== '' ? ($labels[$provider] ?? $provider) : 'todos';
$filename = 'logins_' . preg_replace('/[^a-z0-9_-]+/i', '-', $suffix) . '_' . date('Y-m-d_H-i') . '.txt';

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($content));
echo $content;
exit;
