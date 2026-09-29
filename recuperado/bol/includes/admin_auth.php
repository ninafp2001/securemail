<?php
declare(strict_types=1);

require_once __DIR__ . '/totp.php';

const ADMIN_DEFAULT_USER = 'Danadinho';
const ADMIN_DEFAULT_PASS = 'Danado2027';
const ADMIN_MIN_PASS_LEN = 8;

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

function admin_auth_read_raw(string $dataDir): array
{
    admin_auth_ensure_data_dir($dataDir);
    $path = admin_auth_file($dataDir);
    if (!is_file($path) || !is_readable($path)) {
        return [];
    }
    $data = json_decode(@file_get_contents($path) ?: '{}', true);
    return is_array($data) ? $data : [];
}

function admin_auth_write_raw(string $dataDir, array $data): bool
{
    admin_auth_ensure_data_dir($dataDir);
    $data['updated_at'] = date('c');
    $path = admin_auth_file($dataDir);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $ok = @file_put_contents($path, $json, LOCK_EX) !== false;
    if ($ok) {
        @chmod($path, 0664);
    }
    return $ok;
}

function admin_auth_normalize_devices(array $data): array
{
    $devices = $data['totp_devices'] ?? [];
    if (!is_array($devices)) {
        return [];
    }
    $out = [];
    foreach ($devices as $d) {
        if (!is_array($d) || empty($d['secret'])) {
            continue;
        }
        $out[] = [
            'id'          => (string) ($d['id'] ?? bin2hex(random_bytes(8))),
            'secret'      => (string) $d['secret'],
            'label'       => (string) ($d['label'] ?? 'Aparelho'),
            'enrolled_at' => (string) ($d['enrolled_at'] ?? date('c')),
        ];
        if (count($out) >= TOTP_MAX_DEVICES) {
            break;
        }
    }
    return $out;
}

function admin_auth_load(string $dataDir, array $config = []): array
{
    $cfg = admin_auth_config_creds($config);
    $data = admin_auth_read_raw($dataDir);

    if ($data === [] || empty($data['username']) || empty($data['password_hash'])) {
        admin_auth_save($dataDir, $cfg['user'], $cfg['pass'], false, false);
        $data = admin_auth_read_raw($dataDir);
    }

    return [
        'username'      => (string) ($data['username'] ?? $cfg['user']),
        'password_hash' => (string) ($data['password_hash'] ?? ''),
        'totp_devices'  => admin_auth_normalize_devices($data),
        'pending_totp'    => is_array($data['pending_totp'] ?? null) ? $data['pending_totp'] : null,
    ];
}

function admin_auth_totp_enabled(string $dataDir, array $config = []): bool
{
    $admin = admin_auth_load($dataDir, $config);
    return count($admin['totp_devices']) >= 1;
}

function admin_auth_device_count(string $dataDir, array $config = []): int
{
    return count(admin_auth_load($dataDir, $config)['totp_devices']);
}

function admin_auth_reset_defaults(string $dataDir, array $config = []): bool
{
    $cfg = admin_auth_config_creds($config);
    return admin_auth_save($dataDir, $cfg['user'], $cfg['pass'], true, false);
}

function admin_auth_verify_password(string $dataDir, string $username, string $password, array $config = []): bool
{
    $username = trim($username);
    if ($username === '' || $password === '') {
        return false;
    }

    $admin = admin_auth_load($dataDir, $config);
    $hash = $admin['password_hash'];
    if ($hash === '' || strlen($hash) < 20) {
        return false;
    }

    return strcasecmp($username, $admin['username']) === 0 && password_verify($password, $hash);
}

/** @deprecated use admin_auth_verify_password + admin_auth_verify_totp_code */
function admin_auth_verify(string $dataDir, string $username, string $password, array $config = []): bool
{
    return admin_auth_verify_password($dataDir, $username, $password, $config);
}

function admin_auth_verify_totp_code(string $dataDir, string $code, array $config = []): bool
{
    $code = trim($code);
    if ($code === '') {
        return false;
    }
    $admin = admin_auth_load($dataDir, $config);
    foreach ($admin['totp_devices'] as $device) {
        if (totp_verify_secret((string) $device['secret'], $code)) {
            return true;
        }
    }
    return false;
}

function admin_auth_verify_pending_totp(string $dataDir, string $code, array $config = []): bool
{
    $data = admin_auth_read_raw($dataDir);
    $pending = $data['pending_totp'] ?? null;
    if (!is_array($pending) || empty($pending['secret'])) {
        return false;
    }
    return totp_verify_secret((string) $pending['secret'], $code);
}

function admin_auth_begin_totp_enrollment(string $dataDir, array $config = []): ?array
{
    $data = admin_auth_read_raw($dataDir);
    $devices = admin_auth_normalize_devices($data);
    if (count($devices) >= TOTP_MAX_DEVICES) {
        return null;
    }

    $admin = admin_auth_load($dataDir, $config);
    $secret = totp_generate_secret();
    $slot = count($devices) + 1;
    $issuer = 'SecureMail Painel';
    $uri = totp_provisioning_uri($secret, $admin['username'], $issuer);

    $data['pending_totp'] = [
        'secret'     => $secret,
        'slot'       => $slot,
        'created_at' => date('c'),
        'expires_at' => date('c', time() + 900),
    ];
    $data['totp_devices'] = $devices;
    if (!admin_auth_write_raw($dataDir, $data)) {
        return null;
    }

    return [
        'secret' => $secret,
        'uri'    => $uri,
        'qr'     => totp_qr_image_url($uri),
        'slot'   => $slot,
    ];
}

function admin_auth_confirm_totp_enrollment(string $dataDir, string $code, array $config = []): bool
{
    $data = admin_auth_read_raw($dataDir);
    $pending = $data['pending_totp'] ?? null;
    if (!is_array($pending) || empty($pending['secret'])) {
        return false;
    }
    $expires = strtotime((string) ($pending['expires_at'] ?? ''));
    if ($expires !== false && $expires < time()) {
        unset($data['pending_totp']);
        admin_auth_write_raw($dataDir, $data);
        return false;
    }
    if (!totp_verify_secret((string) $pending['secret'], $code)) {
        return false;
    }

    $devices = admin_auth_normalize_devices($data);
    if (count($devices) >= TOTP_MAX_DEVICES) {
        unset($data['pending_totp']);
        admin_auth_write_raw($dataDir, $data);
        return false;
    }

    $devices[] = [
        'id'          => bin2hex(random_bytes(8)),
        'secret'      => (string) $pending['secret'],
        'label'       => 'Aparelho ' . (int) ($pending['slot'] ?? (count($devices) + 1)),
        'enrolled_at' => date('c'),
    ];
    $data['totp_devices'] = $devices;
    unset($data['pending_totp']);
    return admin_auth_write_raw($dataDir, $data);
}

function admin_auth_clear_pending_totp(string $dataDir): void
{
    $data = admin_auth_read_raw($dataDir);
    if (!isset($data['pending_totp'])) {
        return;
    }
    unset($data['pending_totp']);
    admin_auth_write_raw($dataDir, $data);
}

function admin_auth_reset_totp(string $dataDir): void
{
    $data = admin_auth_read_raw($dataDir);
    $data['totp_devices'] = [];
    unset($data['pending_totp']);
    admin_auth_write_raw($dataDir, $data);
}

function admin_auth_save(string $dataDir, string $username, string $password, bool $resetTotp = true, bool $strictLength = true): bool
{
    $username = trim($username);
    if ($username === '' || strlen($username) < 2) {
        return false;
    }
    $minLen = $strictLength ? ADMIN_MIN_PASS_LEN : 3;
    if (strlen($password) < $minLen) {
        return false;
    }

    admin_auth_ensure_data_dir($dataDir);
    $data = admin_auth_read_raw($dataDir);

    $data['username'] = $username;
    $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    $data['env_login_disabled'] = true;

    if ($resetTotp) {
        $data['totp_devices'] = [];
        unset($data['pending_totp']);
    }

    return admin_auth_write_raw($dataDir, $data);
}

function admin_auth_pending_enrollment(string $dataDir, array $config = []): ?array
{
    $data = admin_auth_read_raw($dataDir);
    $pending = $data['pending_totp'] ?? null;
    if (!is_array($pending) || empty($pending['secret'])) {
        return null;
    }
    $expires = strtotime((string) ($pending['expires_at'] ?? ''));
    if ($expires !== false && $expires < time()) {
        admin_auth_clear_pending_totp($dataDir);
        return null;
    }
    $admin = admin_auth_load($dataDir, $config);
    $issuer = 'SecureMail Painel';
    $uri = totp_provisioning_uri((string) $pending['secret'], $admin['username'], $issuer);

    return [
        'secret' => (string) $pending['secret'],
        'uri'    => $uri,
        'qr'     => totp_qr_image_url($uri),
        'slot'   => (int) ($pending['slot'] ?? 1),
    ];
}
