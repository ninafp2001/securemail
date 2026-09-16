<?php
declare(strict_types=1);

/**
 * Reset login do painel: /panel/reset_painel.php?key=CHAVE_DO_CONFIG
 */

require dirname(__DIR__) . '/includes/panel_init.php';

$config = panel_bootstrap_config();
$key = (string) ($_GET['key'] ?? '');
$expected = (string) ($config['panel_reset_key'] ?? '');

if ($expected === '' || $key === '' || !hash_equals($expected, $key)) {
    http_response_code(403);
    exit('Chave inválida.');
}

$cfg = admin_auth_config_creds($config);
$ok = admin_auth_reset_defaults(panel_data_dir($config), $config);

header('Content-Type: text/plain; charset=utf-8');
if ($ok) {
    echo "OK — painel resetado.\nUsuário: {$cfg['user']}\nSenha: {$cfg['pass']}\n";
} else {
    echo "ERRO — verifique permissões em var/data/\n";
}
