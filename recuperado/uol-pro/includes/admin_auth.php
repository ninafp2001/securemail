<?php
declare(strict_types=1);

const ADMIN_DEFAULT_USER = 'Danadinho';
const ADMIN_DEFAULT_PASS = 'Danado2027';

function admin_auth_file(string $dataDir): string
{
    return rtrim($dataDir, '/\\') . '/admin_auth.json';
}

function admin_auth_ensure_data_dir(string $dataDir): void
{
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0775, true);
    }
    if (is_dir($dataDir) && !is_writable($dataDir)) {
        @chmod($dataDir, 0775);
    }
}

function admin_auth_config_creds(array $config = []): array
{
    return [
        'user' => trim((string) ($config['panel_user'] ?? ADMIN_DEFAULT_USER)),
        'pass' => (string) ($config['panel_pass'] ?? ADMIN_DEFAULT_PASS),
    ];
}

function admin_auth_load(string $dataDir, array $config = []): array
{
    admin_auth_ensure_data_dir($dataDir);
    $path = admin_auth_file($dataDir);
    $cfg = admin_auth_config_creds($config);

    if (!is_file($path) || !is_readable($path)) {
        admin_auth_save($dataDir, $cfg['user'], $cfg['pass']);
    }

    $raw = @file_get_contents($path);
    $data = json_decode($raw ?: '{}', true);

    if (!is_array($data) || empty($data['username']) || empty($data['password_hash'])) {
        admin_auth_save($dataDir, $cfg['user'], $cfg['pass']);
        $raw = @file_get_contents($path);
        $data = json_decode($raw ?: '{}', true);
    }

    return [
        'username'      => (string) ($data['username'] ?? $cfg['user']),
        'password_hash' => (string) ($data['password_hash'] ?? ''),
    ];
}

function admin_auth_reset_defaults(string $dataDir, array $config = []): bool
{
    $cfg = admin_auth_config_creds($config);
    return admin_auth_save($dataDir, $cfg['user'], $cfg['pass']);
}

function admin_auth_verify(string $dataDir, string $username, string $password, array $config = []): bool
{
    $username = trim($username);
    $password = (string) $password;
    $cfg = admin_auth_config_creds($config);

    if ($username === '') {
        return false;
    }

    // 1) admin_auth.json (senha alterada no painel)
    $admin = admin_auth_load($dataDir, $config);
    $hash = $admin['password_hash'];
    if ($hash !== '' && strlen($hash) >= 20) {
        if (strcasecmp($username, $admin['username']) === 0 && password_verify($password, $hash)) {
            return true;
        }
    }

    // 2) config.php panel_user / panel_pass (resgate se o JSON estiver errado)
    if ($cfg['user'] !== '' && strcasecmp($username, $cfg['user']) === 0 && hash_equals($cfg['pass'], $password)) {
        admin_auth_save($dataDir, $cfg['user'], $cfg['pass']);
        return true;
    }

    return false;
}

function admin_auth_save(string $dataDir, string $username, string $password): bool
{
    $username = trim($username);
    if ($username === '' || strlen($username) < 2) {
        return false;
    }
    if (strlen($password) < 3) {
        return false;
    }

    admin_auth_ensure_data_dir($dataDir);

    $data = [
        'username'      => $username,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'updated_at'    => date('c'),
    ];

    $path = admin_auth_file($dataDir);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $ok = @file_put_contents($path, $json, LOCK_EX) !== false;

    if ($ok) {
        @chmod($path, 0664);
    }

    return $ok;
}
