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
        mkdir($dataDir, 0700, true);
    }
}

function admin_auth_config_creds(array $config = []): array
{
    return [
        'user' => trim((string) ($config['panel_user'] ?? ADMIN_DEFAULT_USER)),
        'pass' => (string) ($config['panel_pass'] ?? ADMIN_DEFAULT_PASS),
    ];
}

/** Versão do token — muda ao salvar nova senha (invalida cookies antigos). */
function admin_auth_token_version(string $dataDir, array $config = []): string
{
    $path = admin_auth_file($dataDir);
    if (!is_file($path)) {
        return 'init';
    }

    $data = json_decode((string) @file_get_contents($path), true);
    if (!is_array($data)) {
        return 'init';
    }

    if (!empty($data['auth_version'])) {
        return (string) $data['auth_version'];
    }

    return (string) ($data['updated_at'] ?? 'init');
}

function admin_auth_load(string $dataDir, array $config = []): array
{
    admin_auth_ensure_data_dir($dataDir);
    $path = admin_auth_file($dataDir);
    $cfg = admin_auth_config_creds($config);

    if (!is_file($path)) {
        admin_auth_save($dataDir, $cfg['user'], $cfg['pass']);
    }

    $data = json_decode((string) @file_get_contents($path), true);
    if (is_array($data) && strcasecmp((string) ($data['username'] ?? ''), $cfg['user']) !== 0) {
        admin_auth_save($dataDir, $cfg['user'], $cfg['pass']);
        $data = json_decode((string) @file_get_contents($path), true);
    }
    if (!is_array($data) || empty($data['password_hash'])) {
        admin_auth_save($dataDir, $cfg['user'], $cfg['pass']);
        $data = json_decode((string) @file_get_contents($path), true);
    }

    return [
        'username'      => (string) ($data['username'] ?? $cfg['user']),
        'password_hash' => (string) ($data['password_hash'] ?? ''),
    ];
}

function admin_auth_verify(string $dataDir, string $username, string $password, array $config = []): bool
{
    $username = trim($username);
    if ($username === '' || $password === '') {
        return false;
    }

    $path = admin_auth_file($dataDir);

    if (!is_file($path)) {
        $cfg = admin_auth_config_creds($config);
        return strcasecmp($username, $cfg['user']) === 0 && hash_equals($cfg['pass'], $password);
    }

    $admin = admin_auth_load($dataDir, $config);
    if ($admin['password_hash'] === '') {
        return false;
    }

    return strcasecmp($username, $admin['username']) === 0
        && password_verify($password, $admin['password_hash']);
}

function admin_auth_save(string $dataDir, string $username, string $password): bool
{
    $username = trim($username);
    if ($username === '' || strlen($password) < 4) {
        return false;
    }

    admin_auth_ensure_data_dir($dataDir);
    $json = json_encode([
        'username'      => $username,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'auth_version'  => bin2hex(random_bytes(16)),
        'updated_at'    => date('c'),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    return file_put_contents(admin_auth_file($dataDir), $json, LOCK_EX) !== false;
}

function admin_auth_reset_defaults(string $dataDir, array $config = []): bool
{
    $cfg = admin_auth_config_creds($config);
    return admin_auth_save($dataDir, $cfg['user'], $cfg['pass']);
}
