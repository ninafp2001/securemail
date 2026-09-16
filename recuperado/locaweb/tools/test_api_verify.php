<?php
declare(strict_types=1);

/**
 * Teste CLI da API verify — uso:
 *   php tools/test_api_verify.php email@dominio.com.br senha
 */

if ($argc < 3) {
    fwrite(STDERR, "Uso: php tools/test_api_verify.php <email> <senha>\n");
    exit(1);
}

$email = $argv[1];
$password = $argv[2];

require dirname(__DIR__) . '/src/bootstrap.php';
require dirname(__DIR__) . '/includes/api_auth.php';

$config = app_config();
$result = locaweb_api_verify_login($email, $password, $config, true);

echo json_encode([
    'http_status' => $result['status'],
    'body' => $result['payload'],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

exit($result['status'] === 200 ? 0 : 1);
