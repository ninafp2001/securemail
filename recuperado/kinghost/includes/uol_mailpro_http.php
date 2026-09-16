<?php
declare(strict_types=1);

require_once __DIR__ . '/uol_validate.php';
require_once __DIR__ . '/http_client.php';

/**
 * Valida credenciais na API HTTP do Terra Mail (POST mail.terra.com.br/auth).
 *
 * @return array{ok:bool,message_key:string,http_code:int,text?:string,source:string}
 */
function uol_mailpro_http_verify_login(string $email, string $password, array $config): array
{
    if (!function_exists('curl_init')) {
        return uol_mailpro_fail('connerror', 0, 'Nao foi possivel contactar o servidor Terra Mail.');
    }

    $authUrl = rtrim((string) ($config['uol_mailpro_auth_url'] ?? 'https://mail.terra.com.br/auth'), '/');
    $redirUrl = (string) ($config['uol_mailpro_redir_url'] ?? 'https://mail.terra.com.br/');
    $timeout = (int) ($config['uol_mailpro_timeout'] ?? 12);
    $cookieFile = tempnam(sys_get_temp_dir(), 'terra_auth_');

    if ($cookieFile === false) {
        return uol_mailpro_fail('connerror', 0, 'Nao foi possivel contactar o servidor Terra Mail.');
    }

    try {
        $email = strtolower(trim($email));
        $origin = terra_mailpro_origin($authUrl);

        $postBody = http_build_query([
            'lang'      => '',
            'domain'    => '',
            'redir_url' => $redirUrl,
            'login'     => $email,
            'password'  => $password,
            'submit'    => 'Entrar',
        ]);

        $response = uol_mailpro_http_post($authUrl, $postBody, $cookieFile, $timeout, $origin);
        if ($response === null) {
            return uol_mailpro_fail('connerror', 0, 'Erro de conexao com o servidor Terra Mail.');
        }

        return uol_mailpro_parse_auth_response(
            $authUrl,
            $response['code'],
            $response['headers'],
            $response['body'],
            (string) ($response['effective_url'] ?? $authUrl),
            $cookieFile
        );
    } finally {
        @unlink($cookieFile);
    }
}

function terra_mailpro_origin(string $authUrl): string
{
    $parts = parse_url($authUrl);
    $scheme = (string) ($parts['scheme'] ?? 'https');
    $host = (string) ($parts['host'] ?? 'mail.terra.com.br');
    return $scheme . '://' . $host;
}

/** @return array{ok:bool,message_key:string,http_code:int,text?:string,source:string} */
function uol_mailpro_fail(string $key, int $code, string $text): array
{
    return [
        'ok'          => false,
        'message_key' => $key,
        'http_code'   => $code,
        'text'        => $text,
        'source'      => 'uol_mailpro',
    ];
}

/**
 * @return array{code:int,headers:string,body:string,effective_url?:string}|null
 */
function uol_mailpro_http_post(string $url, string $body, string $cookieFile, int $timeout, string $origin = ''): ?array
{
    $allHeaders = '';
    if ($origin === '') {
        $origin = terra_mailpro_origin($url);
    }
    $referer = rtrim($origin, '/') . '/';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 8,
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
            'Origin: ' . $origin,
            'Referer: ' . $referer,
        ],
        CURLOPT_HEADERFUNCTION => static function ($curl, string $header) use (&$allHeaders): int {
            $allHeaders .= $header;
            return strlen($header);
        },
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
        return [
            'code'          => $code,
            'headers'       => $allHeaders,
            'body'          => $raw,
            'effective_url' => $effectiveUrl,
        ];
    }

    return [
        'code'          => $code,
        'headers'       => $allHeaders !== '' ? $allHeaders : substr($raw, 0, $sepPos),
        'body'          => substr($raw, $sepPos + 4),
        'effective_url' => $effectiveUrl,
    ];
}

/** @return array{ok:bool,message_key:string,http_code:int,text?:string,source:string} */
function uol_mailpro_parse_auth_response(
    string $authUrl,
    int $httpCode,
    string $headers,
    string $body,
    string $effectiveUrl,
    string $cookieFile
): array {
    $onAuthPage = uol_mailpro_is_auth_page($authUrl, $effectiveUrl);
    $messageText = uol_mailpro_extract_message_text($body);
    $hasSession = uol_mailpro_has_session($headers, $cookieFile);

    if ($messageText !== '' && uol_mailpro_is_login_error_text($messageText)) {
        return uol_mailpro_fail('wrong_password', $httpCode, $messageText);
    }

    $bodyLower = mb_strtolower($body, 'UTF-8');
    if ($onAuthPage) {
        if (preg_match('/usu[aá]rio ou senha inv[aá]lidos?/iu', $body, $m)) {
            return uol_mailpro_fail('wrong_password', $httpCode, trim($m[0]));
        }
        if (preg_match('/senha inv[aá]lida/iu', $body, $m)) {
            return uol_mailpro_fail('wrong_password', $httpCode, trim($m[0]));
        }
        if (preg_match('/login inv[aá]lido/iu', $body, $m)) {
            return uol_mailpro_fail('wrong_password', $httpCode, trim($m[0]));
        }
    }

    $leftAuth = !$onAuthPage;
    $terraWebmail = preg_match('#mail\.terra\.com\.br/(webmail|rc|horde|imp|login/check|m/)#i', $effectiveUrl) === 1
        || str_contains(strtolower($effectiveUrl), 'webmailterra');

    if ($httpCode >= 200 && $httpCode < 400 && ($leftAuth || $terraWebmail)) {
        if ($messageText === '' || !uol_mailpro_is_login_error_text($messageText)) {
            return [
                'ok'          => true,
                'message_key' => 'success',
                'http_code'   => $httpCode,
                'source'      => 'uol_mailpro',
            ];
        }
    }

    if ($httpCode >= 200 && $httpCode < 400 && $hasSession && $leftAuth) {
        return [
            'ok'          => true,
            'message_key' => 'success',
            'http_code'   => $httpCode,
            'source'      => 'uol_mailpro',
        ];
    }

    if ($httpCode === 401 || $httpCode === 403) {
        return uol_mailpro_fail('wrong_password', $httpCode, 'Usuário ou senha inválidos.');
    }

    if ($onAuthPage) {
        return uol_mailpro_fail(
            'wrong_password',
            $httpCode,
            $messageText !== '' ? $messageText : 'Usuário ou senha inválidos.'
        );
    }

    return uol_mailpro_fail('connerror', $httpCode, 'Não foi possível validar no Terra Mail Pessoa FÃ­sica. Tente novamente.');
}

function uol_mailpro_is_auth_page(string $authUrl, string $effectiveUrl): bool
{
    $authPath = strtolower(rtrim((string) (parse_url($authUrl, PHP_URL_PATH) ?: '/auth'), '/'));
    $finalPath = strtolower(rtrim((string) (parse_url($effectiveUrl, PHP_URL_PATH) ?: '/'), '/'));

    if ($finalPath === $authPath || str_ends_with(strtolower($effectiveUrl), '/auth')) {
        return true;
    }

    return false;
}

function uol_mailpro_has_session(string $headers, string $cookieFile): bool
{
    if (preg_match('/Set-Cookie:\s*[^=\r\n]*(sess|session|auth|token|JSESSIONID|PHPSESSID|mailpro)[^;\r\n]*/i', $headers)) {
        return true;
    }

    if (!is_readable($cookieFile)) {
        return false;
    }

    $content = (string) file_get_contents($cookieFile);
    foreach (explode("\n", $content) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = preg_split('/\t+/', $line);
        if (!is_array($parts) || count($parts) < 7) {
            continue;
        }

        $name = trim((string) $parts[5]);
        $value = trim((string) $parts[6]);
        if ($value === '' || $name === '' || strcasecmp($name, 'deleted') === 0) {
            continue;
        }

        if (preg_match('/(sess|session|auth|token|jsession|phpsess|mailpro|sid)/i', $name)) {
            return true;
        }
    }

    return false;
}

function uol_mailpro_is_login_error_text(string $text): bool
{
    $lower = mb_strtolower(trim($text), 'UTF-8');
    if ($lower === '') {
        return false;
    }

    return str_contains($lower, 'invál')
        || str_contains($lower, 'invalid')
        || str_contains($lower, 'incorret')
        || str_contains($lower, 'senha')
        || str_contains($lower, 'usuário')
        || str_contains($lower, 'usuario')
        || str_contains($lower, 'login')
        || str_contains($lower, 'bloque')
        || str_contains($lower, 'expirad');
}

function uol_mailpro_extract_message_text(string $body): string
{
    $patterns = [
        '/<div[^>]*id=["\']message["\'][^>]*>(.*?)<\/div>/is',
        '/<div[^>]*id=["\']notice["\'][^>]*>.*?<p[^>]*>(.*?)<\/p>/is',
    ];

    foreach ($patterns as $pattern) {
        if (!preg_match($pattern, $body, $m)) {
            continue;
        }

        $t = trim(preg_replace('/\s+/u', ' ', strip_tags($m[1])));
        if ($t === '' || str_contains(mb_strtolower($t, 'UTF-8'), 'javascript')) {
            continue;
        }

        return html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return '';
}
