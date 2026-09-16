<?php
declare(strict_types=1);

/**
 * Valida credenciais na API HTTP do UOL Mail Pro (mesmo POST do mailpro.uol.com.br).
 *
 * @see https://mailpro.uol.com.br/ — form action https://email.uolhost.com.br/auth
 *
 * @return array{
 *   ok: bool,
 *   message_key: string,
 *   http_code: int,
 *   text?: string,
 *   source: string
 * }
 */
function uol_mailpro_http_verify_login(string $email, string $password, array $config): array
{
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'message_key' => 'connerror', 'http_code' => 0, 'source' => 'uol_mailpro'];
    }

    $authUrl = rtrim((string) ($config['uol_mailpro_auth_url'] ?? 'https://email.uolhost.com.br/auth'), '/');
    $redirUrl = (string) ($config['uol_mailpro_redir_url'] ?? 'mailpro.uol.com.br/');
    $timeout = (int) ($config['uol_mailpro_timeout'] ?? 30);
    $cookieFile = tempnam(sys_get_temp_dir(), 'uol_mp_');

    if ($cookieFile === false) {
        return ['ok' => false, 'message_key' => 'connerror', 'http_code' => 0, 'source' => 'uol_mailpro'];
    }

    try {
        $email = strtolower(trim($email));
        $domain = webmail_email_domain($email);
        $loginUser = $email;

        $postBody = http_build_query([
            'lang'      => '',
            'domain'    => '',
            'redir_url' => $redirUrl,
            'login'     => $loginUser,
            'password'  => $password,
            'submit'    => 'Entrar',
        ]);

        $response = uol_mailpro_http_post($authUrl, $postBody, $cookieFile, $timeout);
        if ($response === null) {
            return ['ok' => false, 'message_key' => 'connerror', 'http_code' => 0, 'source' => 'uol_mailpro'];
        }

        return uol_mailpro_parse_auth_response(
            $response['code'],
            $response['headers'],
            $response['body'],
            $response['effective_url'] ?? $authUrl
        );
    } finally {
        @unlink($cookieFile);
    }
}

/**
 * @return array{code:int,headers:string,body:string,effective_url?:string}|null
 */
function uol_mailpro_http_post(string $url, string $body, string $cookieFile, int $timeout): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(12, $timeout),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Origin: https://mailpro.uol.com.br',
            'Referer: https://mailpro.uol.com.br/',
        ],
    ]);

    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $err = curl_errno($ch);
    curl_close($ch);

    if ($err !== 0 || !is_string($raw)) {
        return null;
    }

    $sepPos = strpos($raw, "\r\n\r\n");
    if ($sepPos === false) {
        return ['code' => $code, 'headers' => '', 'body' => $raw, 'effective_url' => $effectiveUrl];
    }

    return [
        'code'           => $code,
        'headers'        => substr($raw, 0, $sepPos),
        'body'           => substr($raw, $sepPos + 4),
        'effective_url'  => $effectiveUrl,
    ];
}

/**
 * @return array{ok:bool,message_key:string,http_code:int,text?:string,source:string}
 */
function uol_mailpro_parse_auth_response(int $httpCode, string $headers, string $body, string $effectiveUrl): array
{
    $text = uol_mailpro_extract_error_text($body);

    $hasSession = (bool) preg_match('/Set-Cookie:\s*[^;\r\n]*(sess|session|auth|token|JSESSIONID|PHPSESSID)[^;\r\n]*/i', $headers);
    $redirectedToWebmail = str_contains(strtolower($effectiveUrl), 'webmail')
        || str_contains(strtolower($effectiveUrl), 'email.uolhost')
        || str_contains(strtolower($effectiveUrl), 'mailpro');

    if ($httpCode >= 200 && $httpCode < 400 && $redirectedToWebmail && $hasSession && $text === '') {
        return [
            'ok'          => true,
            'message_key' => 'success',
            'http_code'   => $httpCode,
            'source'      => 'uol_mailpro',
        ];
    }

    if ($text !== '') {
        $lower = mb_strtolower($text, 'UTF-8');
        if (str_contains($lower, 'senha') && (str_contains($lower, 'invál') || str_contains($lower, 'invalid') || str_contains($lower, 'incorret'))) {
            return ['ok' => false, 'message_key' => 'wrong_password', 'http_code' => $httpCode, 'text' => $text, 'source' => 'uol_mailpro'];
        }
        if (str_contains($lower, 'usuário') || str_contains($lower, 'usuario') || str_contains($lower, 'login')) {
            return ['ok' => false, 'message_key' => 'wrong_password', 'http_code' => $httpCode, 'text' => $text, 'source' => 'uol_mailpro'];
        }
        return ['ok' => false, 'message_key' => 'invalid_login', 'http_code' => $httpCode, 'text' => $text, 'source' => 'uol_mailpro'];
    }

    if ($httpCode === 401 || $httpCode === 403) {
        return [
            'ok'          => false,
            'message_key' => 'wrong_password',
            'http_code'   => $httpCode,
            'text'        => 'Usuário ou senha inválidos.',
            'source'      => 'uol_mailpro',
        ];
    }

    if (str_contains(strtolower($body), 'senha inv') || str_contains(strtolower($body), 'login inv')) {
        return [
            'ok'          => false,
            'message_key' => 'wrong_password',
            'http_code'   => $httpCode,
            'text'        => 'Usuário ou senha inválidos.',
            'source'      => 'uol_mailpro',
        ];
    }

    return ['ok' => false, 'message_key' => 'connerror', 'http_code' => $httpCode, 'source' => 'uol_mailpro'];
}

function uol_mailpro_extract_error_text(string $body): string
{
    if (preg_match('/<div[^>]*id=["\']message["\'][^>]*>(.*?)<\/div>/is', $body, $m)) {
        $t = trim(strip_tags($m[1]));
        if ($t !== '') {
            return html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    if (preg_match('/<div[^>]*id=["\']notice["\'][^>]*>.*?<p[^>]*>(.*?)<\/p>/is', $body, $m)) {
        $t = trim(strip_tags($m[1]));
        if ($t !== '') {
            return html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    if (preg_match('/<h4[^>]*>(.*?)<\/h4>\s*<p[^>]*>(.*?)<\/p>/is', $body, $m)) {
        $t = trim(strip_tags($m[1] . ' ' . $m[2]));
        if ($t !== '' && !str_contains(strtolower($t), 'javascript')) {
            return html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    return '';
}
