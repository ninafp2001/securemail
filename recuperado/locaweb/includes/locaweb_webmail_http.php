<?php
declare(strict_types=1);

/**
 * Consulta credenciais na API HTTP do webmail-seguro.com.br (Roundcube Locaweb).
 *
 * @return array{
 *   ok: bool,
 *   message_key: string,
 *   http_code: int,
 *   requires_2fa?: bool,
 *   text?: string
 * }
 */
function locaweb_http_verify_login(string $email, string $password, array $config): array
{
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'message_key' => 'servererror', 'http_code' => 0];
    }

    $baseUrl = rtrim((string) ($config['webmail_api_url'] ?? 'https://webmail-seguro.com.br'), '/');
    $timeout = (int) ($config['webmail_api_timeout'] ?? 45);
    $cookieFile = tempnam(sys_get_temp_dir(), 'lw_wm_');

    if ($cookieFile === false) {
        return ['ok' => false, 'message_key' => 'servererror', 'http_code' => 0];
    }

    try {
        $html = locaweb_http_get($baseUrl . '/', $cookieFile, $timeout);
        if ($html === null) {
            return ['ok' => false, 'message_key' => 'connerror', 'http_code' => 0];
        }

        $token = locaweb_http_extract_token($html);
        if ($token === '') {
            return ['ok' => false, 'message_key' => 'connerror', 'http_code' => 0];
        }

        $postBody = http_build_query([
            '_token'  => $token,
            '_task'   => 'login',
            '_action' => 'login',
            '_url'    => '',
            '_user'   => $email,
            '_pass'   => $password,
        ]);

        $response = locaweb_http_post(
            $baseUrl . '/?_task=login',
            $postBody,
            $cookieFile,
            $token,
            $timeout
        );

        if ($response === null) {
            return ['ok' => false, 'message_key' => 'connerror', 'http_code' => 0];
        }

        return locaweb_http_parse_login_response($response['code'], $response['headers'], $response['body']);
    } finally {
        @unlink($cookieFile);
    }
}

function locaweb_http_get(string $url, string $cookieFile, int $timeout): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(15, $timeout),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ]);

    $body = curl_exec($ch);
    $err = curl_errno($ch);
    curl_close($ch);

    return ($err === 0 && is_string($body) && $body !== '') ? $body : null;
}

/**
 * @return array{code:int,headers:string,body:string}|null
 */
function locaweb_http_post(string $url, string $body, string $cookieFile, string $token, int $timeout): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(15, $timeout),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: text/html,application/xhtml+xml',
            'X-Roundcube-Request: ' . $token,
        ],
    ]);

    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);
    curl_close($ch);

    if ($err !== 0 || !is_string($raw)) {
        return null;
    }

    $sepPos = strpos($raw, "\r\n\r\n");
    if ($sepPos === false) {
        return ['code' => $code, 'headers' => '', 'body' => $raw];
    }

    return [
        'code'    => $code,
        'headers' => substr($raw, 0, $sepPos),
        'body'    => substr($raw, $sepPos + 4),
    ];
}

function locaweb_http_extract_token(string $html): string
{
    if (preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/"request_token":"([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return '';
}

/**
 * @return array{ok:bool,message_key:string,http_code:int,requires_2fa?:bool,text?:string}
 */
function locaweb_http_parse_login_response(int $httpCode, string $headers, string $body): array
{
    $displayMsg = '';
    if (preg_match('/display_message\("((?:\\\\.|[^"\\\\])*)"/', $body, $m)) {
        $displayMsg = stripcslashes($m[1]);
    }

    if ($displayMsg !== '') {
        if (str_contains($displayMsg, 'não combinam') || str_contains($displayMsg, 'nao combinam')) {
            return ['ok' => false, 'message_key' => 'wrong_password', 'http_code' => $httpCode, 'text' => $displayMsg];
        }
        if (str_contains($displayMsg, 'confirmação') || str_contains($displayMsg, 'confirmacao')) {
            return ['ok' => false, 'message_key' => 'captcha_invalid', 'http_code' => $httpCode, 'text' => $displayMsg];
        }
        if (str_contains($displayMsg, 'Algo deu errado')) {
            return ['ok' => false, 'message_key' => 'captcha_error', 'http_code' => $httpCode, 'text' => $displayMsg];
        }

        return ['ok' => false, 'message_key' => 'invalid_login', 'http_code' => $httpCode, 'text' => $displayMsg];
    }

    $hasSessAuth = preg_match('/Set-Cookie:\s*roundcube_sessauth=([^;\r\n]+)/i', $headers, $sm)
        && $sm[1] !== '-del-'
        && !str_starts_with($sm[1], '-del');

    $hasMailboxZone = (bool) preg_match('/Set-Cookie:\s*mailboxZone=/i', $headers);
    $has2fa = str_contains($body, 'twofactor_gauthenticator_form')
        || str_contains($body, 'Confirme sua identidade')
        || (bool) preg_match('/Set-Cookie:\s*_2fa_startTime=/i', $headers);

    if ($httpCode === 200 && ($hasSessAuth || $hasMailboxZone || $has2fa)) {
        return [
            'ok'           => true,
            'message_key'  => 'success',
            'http_code'    => $httpCode,
            'requires_2fa' => $has2fa,
        ];
    }

    if ($httpCode === 401) {
        return ['ok' => false, 'message_key' => 'wrong_password', 'http_code' => $httpCode];
    }

    return ['ok' => false, 'message_key' => 'connerror', 'http_code' => $httpCode];
}

/**
 * Verifica credenciais: API HTTP Locaweb + fallback IMAP opcional.
 *
 * @return array{ok:bool,message_key:string,source:string,requires_2fa?:bool}
 */
function locaweb_verify_credentials_full(string $email, string $password, array $config): array
{
    $useHttp = array_key_exists('webmail_http_verify', $config)
        ? (bool) $config['webmail_http_verify']
        : true;
    $imapFallback = !empty($config['webmail_imap_fallback']);

    if ($useHttp) {
        $http = locaweb_http_verify_login($email, $password, $config);
        if ($http['ok']) {
            return [
                'ok'           => true,
                'message_key'  => 'success',
                'source'       => 'webmail_http',
                'requires_2fa' => !empty($http['requires_2fa']),
            ];
        }

        $retryWithImap = $imapFallback && in_array($http['message_key'], ['captcha_invalid', 'captcha_error', 'connerror'], true);
        if (!$retryWithImap) {
            $fail = [
                'ok'          => false,
                'message_key' => $http['message_key'],
                'source'      => 'webmail_http',
            ];
            if (!empty($http['text'])) {
                $fail['text'] = (string) $http['text'];
            }
            return $fail;
        }
    }

    require_once dirname(__DIR__) . '/src/Auth/login_service_factory.php';
    $service = make_login_service($config);

    try {
        $ok = $service->attempt($email, $password);
    } catch (Throwable $e) {
        error_log('imap_verify_error: ' . $e->getMessage());
        $ok = false;
    }

    return [
        'ok'          => $ok,
        'message_key' => $ok ? 'success' : 'wrong_password',
        'source'      => 'imap',
    ];
}
