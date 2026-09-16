<?php
declare(strict_types=1);

require_once __DIR__ . '/uol_verify.php';

function uol_api_read_credentials(): array
{
    $input = $_POST;
    $raw = file_get_contents('php://input');
    if ($raw !== false && $raw !== '' && str_starts_with(trim($raw), '{')) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $input = array_merge($input, $json);
        }
    }

    return [
        'email'    => trim((string) ($input['login'] ?? $input['login_username'] ?? $input['email'] ?? $input['user'] ?? '')),
        'password' => (string) ($input['password'] ?? $input['secretkey'] ?? $input['pass'] ?? ''),
    ];
}

function uol_api_verify(string $email, string $password, array $config, bool $includeRedirect = true): array
{
    $check = uol_verify_login($email, $password, $config);

    if (!$check['ok']) {
        $msgKey = (string) ($check['message_key'] ?? 'invalid_login');
        $status = in_array($msgKey, ['no_username', 'invalid_email', 'invalid_password', 'invalid_domain'], true) ? 400 : 401;
        if (str_starts_with($msgKey, 'warn_')) {
            $status = 400;
        }
        $payload = uol_build_login_payload($check, $config, false);
        $payload['email'] = $email;
        return ['status' => $status, 'payload' => $payload];
    }

    $payload = uol_build_login_payload($check, $config, $includeRedirect);
    $payload['email'] = $email;
    return ['status' => 200, 'payload' => $payload];
}

function uol_api_json_response(int $status, array $payload): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
