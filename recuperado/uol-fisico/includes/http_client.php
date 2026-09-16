<?php
declare(strict_types=1);

/** Cliente HTTP compartilhado para drivers de API. */
function api_http_request(string $method, string $url, array $opts = []): ?array
{
    if (!function_exists('curl_init')) {
        return null;
    }

    $headers = (array) ($opts['headers'] ?? []);
    $body = $opts['body'] ?? null;
    $cookieFile = (string) ($opts['cookie_file'] ?? '');
    $timeout = (int) ($opts['timeout'] ?? 30);
    $follow = (bool) ($opts['follow'] ?? true);
    $extraHeaders = '';

    $ch = curl_init($url);
    $curlOpts = [
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_MAXREDIRS      => (int) ($opts['max_redirs'] ?? 8),
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(12, $timeout),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT      => (string) ($opts['user_agent'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_HEADERFUNCTION => static function ($curl, string $header) use (&$extraHeaders): int {
            $extraHeaders .= $header;
            return strlen($header);
        },
    ];

    if ($cookieFile !== '') {
        $curlOpts[CURLOPT_COOKIEJAR] = $cookieFile;
        $curlOpts[CURLOPT_COOKIEFILE] = $cookieFile;
    }

    if ($body !== null && strtoupper($method) !== 'GET') {
        $curlOpts[CURLOPT_POSTFIELDS] = $body;
    }

    curl_setopt_array($ch, $curlOpts);
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $err = curl_errno($ch);
    curl_close($ch);

    if ($err !== 0 || !is_string($raw)) {
        return null;
    }

    $sepPos = strpos($raw, "\r\n\r\n");
    $respHeaders = $extraHeaders !== '' ? $extraHeaders : ($sepPos !== false ? substr($raw, 0, $sepPos) : '');
    $respBody = $sepPos !== false ? substr($raw, $sepPos + 4) : $raw;

    return [
        'code'           => $code,
        'headers'        => $respHeaders,
        'body'           => $respBody,
        'effective_url'  => $effectiveUrl,
    ];
}

function api_decode_json(string $body): ?array
{
    $body = trim($body);
    if ($body === '' || $body[0] !== '{' && $body[0] !== '[') {
        return null;
    }
    $json = json_decode($body, true);
    return is_array($json) ? $json : null;
}

function api_fail(string $key, string $text, string $source): array
{
    return ['ok' => false, 'message_key' => $key, 'text' => $text, 'source' => $source];
}

function api_ok(string $source): array
{
    return ['ok' => true, 'message_key' => 'success', 'source' => $source];
}
