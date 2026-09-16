<?php
declare(strict_types=1);

/**
 * Reset do login do painel (use 1x se não conseguir entrar).
 * URL: /2/consolelima/reset_painel.php?key=SUA_CHAVE
 * Chave em config.php → panel_reset_key
 */

$config = require dirname(__DIR__) . '/config.php';
if (!is_array($config)) {
    $config = $GLOBALS['projeto2_config'] ?? [];
}

$key = (string) ($_GET['key'] ?? '');
$expected = (string) ($config['panel_reset_key'] ?? 'd3vl1ma2024');

if ($key === '' || !hash_equals($expected, $key)) {
    http_response_code(403);
    exit('Chave inválida.');
}

$cfg = admin_auth_config_creds($config);
$ok = admin_auth_reset_defaults((string) ($config['data_dir'] ?? ''), $config);

header('Content-Type: text/plain; charset=utf-8');
if ($ok) {
    echo "OK — Painel resetado.\n";
    echo "Usuario: {$cfg['user']}\n";
    echo "Senha: {$cfg['pass']}\n";
    echo "Entre em: login.php\n";
} else {
    echo "ERRO — Verifique permissao da pasta data/\n";
}
