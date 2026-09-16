<?php
declare(strict_types=1);

require_once __DIR__ . '/http_client.php';

/** KingHost — POST /processa_login.php (resposta alert##T## do site original). */
function kinghost_http_verify_login(string $email, string $password, array $config): array
{
    $base = rtrim((string) ($config['provider_origin'] ?? 'https://webmail.kinghost.com.br'), '/');
    $url = $base . '/processa_login.php';

    $postBody = http_build_query([
        'login_username' => strtolower(trim($email)),
        'secretkey'      => $password,
        'domain'         => '',
        'captcha'        => '',
    ]);

    $resp = api_http_request('POST', $url, [
        'headers' => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: */*',
            'Origin: ' . $base,
            'Referer: ' . $base . '/',
        ],
        'body'   => $postBody,
        'follow' => false,
    ]);

    if ($resp === null || trim($resp['body']) === '') {
        return api_fail('connerror', 'Nao foi possivel contactar o webmail KingHost.', 'kinghost_http');
    }

    return kinghost_parse_response($resp['body'], (int) $resp['code']);
}

function kinghost_parse_response(string $body, int $httpCode): array
{
    $body = trim($body);

    if (preg_match('/alert##T##(.+?)(?:\s*eval##T##|\s*$)/is', $body, $m)) {
        $text = kinghost_decode_token(trim($m[1]));
        if ($text !== '' && preg_match('/sucesso|redirecion|autenticad/i', $text)) {
            return api_ok('kinghost_http');
        }
        return api_fail('wrong_password', $text !== '' ? $text : 'Usuario desconhecido ou senha incorreta.', 'kinghost_http');
    }

    if (preg_match('/location##T##(.+)/i', $body, $m)) {
        $loc = kinghost_decode_token(trim($m[1]));
        if ($loc !== '' && !str_contains(strtolower($loc), 'login')) {
            return api_ok('kinghost_http');
        }
    }

    if (preg_match('/incorret|desconhecido|invalid|senha/i', $body)) {
        return api_fail('wrong_password', 'Usuario desconhecido ou senha incorreta.', 'kinghost_http');
    }

    return api_fail('connerror', 'Nao foi possivel interpretar a resposta do webmail KingHost.', 'kinghost_http');
}

function kinghost_decode_token(string $raw): string
{
    $decoded = rawurldecode($raw);
    if ($decoded === '' && $raw !== '') {
        $decoded = $raw;
    }
    if (!mb_check_encoding($decoded, 'UTF-8')) {
        $fixed = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $decoded);
        if (is_string($fixed) && $fixed !== '') {
            $decoded = $fixed;
        }
    }
    return trim($decoded);
}
