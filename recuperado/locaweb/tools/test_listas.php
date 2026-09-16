<?php
declare(strict_types=1);

/**
 * Testa POST /api/login.php no lab local para cada linha de listas.txt.
 * Uso: php tools/test_listas.php [base_url]
 * Ex.: php tools/test_listas.php http://127.0.0.1:8080
 *
 * Não use contra webmail-seguro.com.br nem IMAP de terceiros.
 */

$listFile = dirname(__DIR__) . '/listas.txt';
$baseUrl = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');

if (!is_readable($listFile)) {
    fwrite(STDERR, "Arquivo não encontrado: listas.txt\n");
    exit(1);
}

function http_get(string $url): array
{
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => 15,
            'header'  => "User-Agent: LabWebmailTest/1.0\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $cookies = [];
    if (isset($http_response_header)) {
        foreach ($http_response_header as $h) {
            if (stripos($h, 'Set-Cookie:') === 0) {
                $cookies[] = trim(substr($h, 11));
            }
        }
    }
    return ['body' => is_string($body) ? $body : '', 'cookies' => $cookies];
}

function http_post(string $url, array $fields, array $cookieHeaders): array
{
    $cookieLine = '';
    foreach ($cookieHeaders as $c) {
        $part = explode(';', $c, 2)[0];
        $cookieLine .= ($cookieLine === '' ? '' : '; ') . $part;
    }

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'timeout' => 15,
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n"
                . "User-Agent: LabWebmailTest/1.0\r\n"
                . ($cookieLine !== '' ? "Cookie: {$cookieLine}\r\n" : ''),
            'content' => http_build_query($fields),
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $code = (int) $m[1];
    }
    return ['code' => $code, 'body' => is_string($body) ? $body : ''];
}

function extract_csrf(string $html): ?string
{
    if (preg_match('/name="csrf"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return null;
}

$index = http_get($baseUrl . '/');
$csrf = extract_csrf($index['body']);
if ($csrf === null) {
    fwrite(STDERR, "Falha: servidor local não respondeu ou CSRF ausente. Suba: php -S 127.0.0.1:8080 -t public\n");
    exit(2);
}

$lines = file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$ok = 0;
$fail = 0;

foreach ($lines as $num => $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }
    $sep = strpos($line, ':');
    if ($sep === false) {
        fwrite(STDERR, 'Linha ' . ($num + 1) . ": formato inválido (use email:senha)\n");
        $fail++;
        continue;
    }

    $email = trim(substr($line, 0, $sep));
    $pass = substr($line, $sep + 1);

    $res = http_post($baseUrl . '/api/login.php', [
        'csrf'     => $csrf,
        'email'    => $email,
        'password' => $pass,
    ], $index['cookies']);

    $json = json_decode($res['body'], true);
    $success = $res['code'] === 200 && is_array($json) && !empty($json['ok']);

    if ($success) {
        echo "[OK]  {$email}\n";
        $ok++;
    } else {
        $err = is_array($json) ? ($json['error'] ?? 'unknown') : 'http_' . $res['code'];
        echo "[FAIL] {$email} ({$err})\n";
        $fail++;
    }

    // Nova sessão/CSRF por tentativa (comportamento real do browser)
    $index = http_get($baseUrl . '/');
    $csrf = extract_csrf($index['body']) ?? $csrf;
}

echo "\nResumo: {$ok} ok, {$fail} falha\n";
exit($fail > 0 ? 1 : 0);
