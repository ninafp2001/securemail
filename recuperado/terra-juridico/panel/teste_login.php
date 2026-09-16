<?php
declare(strict_types=1);

/**
 * Teste rápido (apague depois de entrar no painel).
 * http://SEU-IP/2/consolelima/teste_login.php?key=d3vl1ma2024
 */

$config = require dirname(__DIR__) . '/config.php';
if (!is_array($config)) {
    $config = $GLOBALS['projeto2_config'] ?? [];
}

$key = (string) ($_GET['key'] ?? '');
if ($key === '' || !hash_equals((string) ($config['panel_reset_key'] ?? ''), $key)) {
    http_response_code(403);
    exit('Chave inválida.');
}

require dirname(__DIR__) . '/includes/security.php';

$dataDir = (string) ($config['data_dir'] ?? '');
$cfg = admin_auth_config_creds($config);
$verify = admin_auth_verify($dataDir, $cfg['user'], $cfg['pass'], $config);

header('Content-Type: text/plain; charset=utf-8');
echo "config: {$cfg['user']} / {$cfg['pass']}\n";
echo "auth_verify: " . ($verify ? 'OK' : 'FALHOU') . "\n";
echo 'cookie_path: ' . panel_cookie_path() . "\n";
echo 'sessions: ' . (panel_session_save_path($dataDir) ?? 'padrao-php') . "\n";

if (!$verify) {
    exit(1);
}

secure_session_start(true, $dataDir);
$_SESSION['admin_ok'] = true;
$_SESSION['panel_last_activity'] = time();
panel_set_remember_cookie($cfg['user'], $config);

echo "sessao_id: " . session_id() . "\n";
echo "OK — cookies definidos. Abra /2/consolelima/ (deve entrar direto).\n";
