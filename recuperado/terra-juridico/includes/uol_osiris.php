<?php
declare(strict_types=1);

require_once __DIR__ . '/http_client.php';

/**
 * UOL Osiris — POST /auth e /auth/user (conta.uol.com.br).
 * Resposta JSON: status 200/600 = senha invalida; dest = sucesso.
 */
function uol_osiris_verify_login(string $email, string $password, array $config): array
{
    $theme = (string) ($config['osiris_theme'] ?? 'default');
    $loginPath = (string) ($config['provider_login_path'] ?? '/login?t=default');
    $origin = rtrim((string) ($config['provider_origin'] ?? 'https://conta.uol.com.br'), '/');
    $loginUrl = $origin . $loginPath;
    $dest = (string) ($config['osiris_dest'] ?? '');

    $cookieFile = tempnam(sys_get_temp_dir(), 'uol_os_');
    if ($cookieFile === false) {
        return api_fail('connerror', 'Nao foi possivel contactar o UOL.', 'uol_osiris');
    }

    try {
        $warm = api_http_request('GET', $loginUrl, [
            'cookie_file' => $cookieFile,
            'headers'     => ['Accept: text/html,application/xhtml+xml'],
        ]);
        if ($warm === null) {
            return api_fail('connerror', 'Nao foi possivel contactar o UOL.', 'uol_osiris');
        }

        if ($dest === '' && preg_match('/"dest"\s*:\s*"([^"]+)"/', $warm['body'], $dm)) {
            $dest = $dm[1];
        }

        $fields = uol_osiris_build_fields($email, $password, $dest, $warm['body']);

        $userResp = uol_osiris_post_json($origin, '/auth/user', $theme, $fields, $cookieFile, $loginUrl);
        if ($userResp !== null) {
            $parsed = uol_osiris_parse_json($userResp, false);
            if ($parsed !== null) {
                if (!empty($parsed['ok'])) {
                    if (!empty($parsed['needs_password'])) {
                        // continua para /auth
                    } elseif ($parsed['ok'] === true) {
                        return api_ok('uol_osiris');
                    }
                } elseif (!empty($parsed['text'])) {
                    return api_fail('wrong_password', $parsed['text'], 'uol_osiris');
                }
            }
        }

        $authResp = uol_osiris_post_json($origin, '/auth', $theme, $fields, $cookieFile, $loginUrl);
        if ($authResp === null) {
            return api_fail('connerror', 'Erro de conexao com o UOL.', 'uol_osiris');
        }

        $final = uol_osiris_parse_json($authResp, true);
        if ($final === null) {
            return api_fail('connerror', 'Resposta invalida do UOL.', 'uol_osiris');
        }
        if (!empty($final['ok'])) {
            return api_ok('uol_osiris');
        }

        return api_fail(
            'wrong_password',
            (string) ($final['text'] ?? 'Usuario ou senha invalidos.'),
            'uol_osiris'
        );
    } finally {
        @unlink($cookieFile);
    }
}

function uol_osiris_build_fields(string $email, string $password, string $dest, string $html): array
{
    $correlation = '';
    if (preg_match('/"correlationId"\s*:\s*"([^"]+)"/', $html, $m)) {
        $correlation = $m[1];
    }

    return array_filter([
        'user'              => strtolower(trim($email)),
        'pass'              => $password,
        'username'          => strtolower(trim($email)),
        'password'          => $password,
        'dest'              => $dest,
        'deviceId'          => bin2hex(random_bytes(16)),
        'captchaResponse'   => null,
        'correlationId'     => $correlation !== '' ? $correlation : null,
        'template'          => 'default',
    ], static fn ($v) => $v !== null);
}

function uol_osiris_post_json(
    string $origin,
    string $path,
    string $theme,
    array $fields,
    string $cookieFile,
    string $referer
): ?array {
    $url = $origin . $path . '?t=' . rawurlencode($theme) . '&legacy=false';
    $body = json_encode($fields, JSON_UNESCAPED_UNICODE);
    if ($body === false) {
        return null;
    }

    return api_http_request('POST', $url, [
        'cookie_file' => $cookieFile,
        'body'        => $body,
        'headers'     => [
            'Content-Type: application/json',
            'Accept: application/json, text/plain, */*',
            'Origin: ' . $origin,
            'Referer: ' . $referer,
        ],
        'follow' => false,
    ]);
}

/** @return array{ok?:bool,needs_password?:bool,text?:string}|null */
function uol_osiris_parse_json(array $resp, bool $allowDest): ?array
{
    $code = (int) ($resp['code'] ?? 0);
    $data = api_decode_json((string) ($resp['body'] ?? ''));
    if (!is_array($data)) {
        if ($code >= 400) {
            return ['text' => 'Usuario ou senha invalidos.'];
        }
        return null;
    }

    if ($allowDest && isset($data['dest']) && is_string($data['dest']) && $data['dest'] !== '') {
        return ['ok' => true];
    }

    if (isset($data['location']) && is_string($data['location']) && $data['location'] !== '') {
        return ['ok' => true];
    }

    $status = (int) ($data['status'] ?? -1);
    $info = trim((string) ($data['info'] ?? ''));

    if ($status === 1 || $status === 2) {
        return ['ok' => true, 'needs_password' => true];
    }

    if (in_array($status, [200, 600, 401, 403, 51], true)) {
        return ['text' => uol_osiris_status_text($status, $info)];
    }

    if ($info !== '') {
        return ['text' => $info];
    }

    if ($status === 0 && $info === '') {
        return ['text' => 'Usuario ou senha invalidos.'];
    }

    if ($code >= 200 && $code < 300 && $status >= 0) {
        return ['ok' => true];
    }

    return ['text' => 'Usuario ou senha invalidos.'];
}

function uol_osiris_status_text(int $status, string $info): string
{
    if ($info !== '') {
        return $info;
    }

    return match ($status) {
        200, 600 => 'Usuario ou senha invalidos.',
        51       => 'Verifique seus dados de acesso.',
        default  => 'Usuario ou senha invalidos.',
    };
}

function bol_osiris_verify_login(string $email, string $password, array $config): array
{
    $config['osiris_theme'] = 'bol';
    $config['provider_login_path'] = '/login?t=bol&env=visitante&dest=https://bmail.uol.com.br/login/check_session';
    $config['osiris_dest'] = 'https://bmail.uol.com.br/login/check_session';

    $domain = strtolower((string) substr(strrchr(strtolower(trim($email)), '@') ?: '', 1));
    if ($domain !== 'bol.com.br' && !str_ends_with($domain, '.bol.com.br')) {
        return api_fail('invalid_email', 'Use seu e-mail @bol.com.br.', 'bol_osiris');
    }

    $result = uol_osiris_verify_login($email, $password, $config);
    if (!$result['ok'] && ($result['message_key'] ?? '') === 'connerror') {
        return uol_osiris_imap_fallback($email, $password, ['imap.bol.com.br', 'mail.bol.com.br']);
    }
    return $result;
}

function uol_fisico_osiris_verify_login(string $email, string $password, array $config): array
{
    $config['osiris_theme'] = 'default';
    $config['provider_login_path'] = '/login?t=default';
    $result = uol_osiris_verify_login($email, $password, $config);
    if (!$result['ok'] && ($result['message_key'] ?? '') === 'connerror') {
        return uol_osiris_imap_fallback($email, $password, ['imap.uol.com.br', 'mail.uol.com.br']);
    }
    return $result;
}

function uol_osiris_imap_fallback(string $email, string $password, array $hosts): array
{
    require_once __DIR__ . '/imap_verify.php';
    if (imap_provider_verify($email, $password, $hosts)) {
        return api_ok('uol_imap');
    }
    return api_fail('wrong_password', 'Usuario ou senha invalidos.', 'uol_imap');
}
