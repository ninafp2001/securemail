<?php
declare(strict_types=1);

require_once __DIR__ . '/webmail_login_validate.php';
require_once __DIR__ . '/webmail_redirect.php';
require_once __DIR__ . '/provider_verify.php';

/**
 * Extrai e-mail e senha do POST/JSON.
 *
 * @return array{email:string,password:string}
 */
function webmail_api_read_credentials(): array
{
    $input = $_POST;

    $raw = file_get_contents('php://input');
    if ($raw !== false && $raw !== '' && str_starts_with(trim($raw), '{')) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $input = array_merge($input, $json);
        }
    }

    $email = trim((string) ($input['email'] ?? $input['user'] ?? $input['_user'] ?? ''));
    $password = (string) ($input['password'] ?? $input['pass'] ?? $input['_pass'] ?? '');

    return ['email' => $email, 'password' => $password];
}

/**
 * Valida e-mail/senha via API UOL Mail Pro (+ IMAP fallback opcional).
 *
 * @return array{status:int,payload:array<string,mixed>}
 */
function webmail_api_verify_login(string $email, string $password, array $config, bool $includeRedirect = true): array
{
    $locale = (string) ($config['default_locale'] ?? 'pt_br');
    $check = webmail_verify_credentials_full($email, $password, $config, $locale);

    if (!$check['ok']) {
        $msgKey = (string) ($check['message_key'] ?? 'invalid_login');
        $status = in_array($msgKey, ['no_username', 'invalid_email', 'invalid_password', 'invalid_domain'], true) ? 400 : 401;
        if (str_starts_with($msgKey, 'warn_')) {
            $status = 400;
        }

        $payload = webmail_build_login_payload($check, $config, $locale, false);
        $payload['email'] = $email;

        return ['status' => $status, 'payload' => $payload];
    }

    $payload = webmail_build_login_payload($check, $config, $locale, $includeRedirect);
    $payload['email'] = $email;

    return ['status' => 200, 'payload' => $payload];
}

/** @return array<string, mixed> */
function webmail_api_json(bool $ok, string $message, ?string $redirect = null): array
{
    $payload = [
        'ok'      => $ok,
        'message' => $message,
        'result'  => $ok ? 'VALID' : 'INVALID',
    ];

    if ($redirect !== null && $redirect !== '') {
        $payload['redirect'] = $redirect;
    }

    return $payload;
}

function webmail_api_json_response(int $status, array $payload): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
