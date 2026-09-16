<?php
declare(strict_types=1);

/** Validacao KingHost — POST original /processa_login.php (resposta identica ao site). */
function kinghost_http_verify_login(string $email, string $password, array $config): array
{
    if (!function_exists('curl_init')) {
        return kinghost_fail('connerror', 'Nao foi possivel contactar o servidor KingHost.');
    }

    $base = rtrim((string) ($config['provider_origin'] ?? 'https://webmail.kinghost.com.br'), '/');
    $url = $base . '/processa_login.php';

    $postBody = http_build_query([
        'login_username' => strtolower(trim($email)),
        'secretkey'      => $password,
        'domain'         => '',
        'captcha'        => '',
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postBody,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 12,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: */*',
            'Origin: ' . $base,
            'Referer: ' . $base . '/',
        ],
    ]);

    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if (!is_string($body) || trim($body) === '') {
        return kinghost_fail('connerror', $err !== '' ? 'Erro de conexao com o webmail KingHost.' : 'Resposta vazia do webmail KingHost.');
    }

    return kinghost_parse_response($body, $code);
}

function kinghost_parse_response(string $body, int $httpCode): array
{
    $body = trim($body);

    if (preg_match('/popula_div##T##/i', $body)
        && preg_match('/roundcube|wm_login|forms\[.?wm_login.?\]\.submit/i', $body)) {
        return ['ok' => true, 'message_key' => 'success', 'source' => 'kinghost_http'];
    }

    if (preg_match('/alert##T##(.+?)(?:\s*eval##T##|\s*$)/is', $body, $m)) {
        $text = kinghost_decode_token(trim($m[1]));
        if ($text !== '' && preg_match('/sucesso|redirecion|autenticad/i', $text)) {
            return ['ok' => true, 'message_key' => 'success', 'source' => 'kinghost_http'];
        }
        return kinghost_fail(
            'wrong_password',
            $text !== '' ? $text : 'Usuário desconhecido ou senha incorreta'
        );
    }

    if (preg_match('/location##T##(.+)/i', $body, $m)) {
        $loc = kinghost_decode_token(trim($m[1]));
        if ($loc !== '' && !str_contains(strtolower($loc), 'login')) {
            return ['ok' => true, 'message_key' => 'success', 'source' => 'kinghost_http'];
        }
        return kinghost_fail('wrong_password', 'Usuário desconhecido ou senha incorreta');
    }

    if (preg_match('/incorret|desconhecido|invalid|senha incorreta/i', $body)) {
        return kinghost_fail('wrong_password', 'Usuário desconhecido ou senha incorreta');
    }

    return kinghost_fail('connerror', 'Nao foi possivel interpretar a resposta do webmail KingHost.');
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

function kinghost_fail(string $key, string $text): array
{
    return ['ok' => false, 'message_key' => $key, 'text' => $text, 'source' => 'kinghost_http'];
}
