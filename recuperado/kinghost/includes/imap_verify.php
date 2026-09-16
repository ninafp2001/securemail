<?php
declare(strict_types=1);

const IMAP_PROVIDER_CONNECT_SEC = 2;
const IMAP_PROVIDER_IO_SEC = 2;
const IMAP_PROVIDER_BUDGET_SEC = 7;

function imap_provider_verify(string $email, string $password, array $hosts): bool
{
    $email = trim($email);
    $password = (string) $password;
    if ($email === '' || $password === '') {
        return false;
    }

    $deadline = microtime(true) + IMAP_PROVIDER_BUDGET_SEC;
    $hosts = array_values(array_unique(array_filter(array_map('strtolower', $hosts))));

    if (imap_provider_parallel($email, $password, $hosts, $deadline)) {
        return true;
    }

    $domain = imap_provider_domain($email);
    if ($domain !== '' && microtime(true) < $deadline) {
        if (pop3_provider_try('pop.terra.com.br', 995, $email, $password, true, $deadline)) {
            return true;
        }
        if (pop3_provider_try('mail.' . $domain, 995, $email, $password, true, $deadline)) {
            return true;
        }
    }

    return false;
}

function imap_provider_parallel(string $email, string $password, array $hosts, float $deadline): bool
{
    $ctx = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ]);

    $pending = [];
    $asyncFlag = defined('STREAM_CLIENT_ASYNC_CONNECT') ? STREAM_CLIENT_ASYNC_CONNECT : 0;

    foreach ($hosts as $host) {
        if (microtime(true) >= $deadline) {
            break;
        }
        $fp = @stream_socket_client(
            'ssl://' . $host . ':993',
            $errno,
            $errstr,
            0.05,
            STREAM_CLIENT_CONNECT | $asyncFlag,
            $ctx
        );
        if (is_resource($fp)) {
            stream_set_blocking($fp, false);
            $pending[$host] = $fp;
        }
    }

    if ($pending === []) {
        foreach ($hosts as $host) {
            if (microtime(true) >= $deadline) {
                break;
            }
            if (imap_provider_try_host($email, $password, $host, 993, true, $deadline)) {
                return true;
            }
        }
        return false;
    }

    while ($pending !== [] && microtime(true) < $deadline) {
        $read = array_values($pending);
        $write = array_values($pending);
        $except = null;
        $left = $deadline - microtime(true);
        if ($left <= 0) {
            break;
        }
        $sec = (int) $left;
        $usec = (int) (($left - $sec) * 1_000_000);
        if ($sec === 0 && $usec < 50_000) {
            $usec = 50_000;
        }

        $n = @stream_select($read, $write, $except, $sec, min($usec, 150000));
        if ($n === false || $n === 0) {
            continue;
        }

        foreach ($pending as $host => $fp) {
            if (!is_resource($fp)) {
                continue;
            }
            stream_set_blocking($fp, true);
            stream_set_timeout($fp, IMAP_PROVIDER_IO_SEC);

            $greet = imap_provider_read_line($fp, $deadline);
            if ($greet === '') {
                fclose($fp);
                unset($pending[$host]);
                continue;
            }

            if (imap_provider_do_login($fp, $email, $password, $deadline)) {
                foreach ($pending as $f) {
                    if (is_resource($f)) {
                        @fwrite($f, "A999 LOGOUT\r\n");
                        @fclose($f);
                    }
                }
                return true;
            }

            fclose($fp);
            unset($pending[$host]);
        }
    }

    foreach ($pending as $fp) {
        if (is_resource($fp)) {
            fclose($fp);
        }
    }

    return false;
}

function imap_provider_domain(string $email): string
{
    $email = strtolower(trim($email));
    if (!str_contains($email, '@')) {
        return '';
    }
    return (string) substr($email, strrpos($email, '@') + 1);
}

function imap_provider_try_host(string $email, string $password, string $host, int $port, bool $ssl, float $deadline): bool
{
    $ctx = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ]);

    $target = ($ssl ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $left = max(1, (int) ($deadline - microtime(true)));
    $fp = @stream_socket_client($target, $errno, $errstr, min(IMAP_PROVIDER_CONNECT_SEC, $left), STREAM_CLIENT_CONNECT, $ctx);
    if (!is_resource($fp)) {
        return false;
    }

    stream_set_timeout($fp, IMAP_PROVIDER_IO_SEC);
    $greet = imap_provider_read_line($fp, $deadline);
    if ($greet === '') {
        fclose($fp);
        return false;
    }

    $tag = 'A001';
    if (!imap_provider_do_login($fp, $email, $password, $deadline)) {
        fclose($fp);
        return false;
    }

    @fwrite($fp, "A999 LOGOUT\r\n");
    fclose($fp);
    return true;
}

function imap_provider_do_login($fp, string $email, string $password, float $deadline): bool
{
    $tag = 'A001';
    fwrite($fp, $tag . ' LOGIN ' . imap_provider_quote($email) . ' ' . imap_provider_quote($password) . "\r\n");
    $resp = '';
    while (microtime(true) < $deadline && !feof($fp)) {
        $line = imap_provider_read_line($fp, $deadline);
        if ($line === '') {
            break;
        }
        $resp .= $line;
        if (str_contains($resp, $tag . ' OK')) {
            return true;
        }
        if (str_contains($resp, $tag . ' NO') || str_contains($resp, $tag . ' BAD')) {
            return false;
        }
    }

    return false;
}

function pop3_provider_try(string $host, int $port, string $email, string $password, bool $ssl, float $deadline): bool
{
    if (microtime(true) >= $deadline) {
        return false;
    }

    $ctx = $ssl ? stream_context_create([
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
    ]) : null;

    $target = ($ssl ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $left = max(1, (int) ($deadline - microtime(true)));
    $fp = @stream_socket_client($target, $errno, $errstr, min(IMAP_PROVIDER_CONNECT_SEC, $left), STREAM_CLIENT_CONNECT, $ctx);
    if (!is_resource($fp)) {
        return false;
    }

    stream_set_timeout($fp, IMAP_PROVIDER_IO_SEC);
    $greet = imap_provider_read_line($fp, $deadline);
    if ($greet === '' || !str_starts_with($greet, '+OK')) {
        fclose($fp);
        return false;
    }

    fwrite($fp, 'USER ' . $email . "\r\n");
    if (!pop3_provider_expect_ok($fp, $deadline)) {
        fclose($fp);
        return false;
    }

    fwrite($fp, 'PASS ' . $password . "\r\n");
    $ok = pop3_provider_expect_ok($fp, $deadline);
    @fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return $ok;
}

function pop3_provider_expect_ok($fp, float $deadline): bool
{
    $line = imap_provider_read_line($fp, $deadline);
    return $line !== '' && str_starts_with($line, '+OK');
}

function imap_provider_quote(string $value): string
{
    return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
}

/** @return bool|null true=exists, false=invalid, null=unknown */
function imap_probe_user_exists(string $email, string $host, int $port = 993): ?bool
{
    $email = strtolower(trim($email));
    if ($email === '') {
        return false;
    }

    $ctx = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ]);

    $target = 'ssl://' . $host . ':' . $port;
    $fp = @stream_socket_client($target, $errno, $errstr, 6, STREAM_CLIENT_CONNECT, $ctx);
    if (!is_resource($fp)) {
        return null;
    }

    stream_set_timeout($fp, 6);
    $greet = imap_provider_read_line($fp, microtime(true) + 6);
    if ($greet === '') {
        fclose($fp);
        return null;
    }

    $pass = 'Probe_' . bin2hex(random_bytes(6));
    $tag = 'P001';
    fwrite($fp, $tag . ' LOGIN ' . imap_provider_quote($email) . ' ' . imap_provider_quote($pass) . "\r\n");

    $resp = '';
    $deadline = microtime(true) + 6;
    while (microtime(true) < $deadline && !feof($fp)) {
        $line = imap_provider_read_line($fp, $deadline);
        if ($line === '') {
            break;
        }
        $resp .= $line;
        if (str_starts_with($line, $tag . ' ')) {
            break;
        }
    }

    @fwrite($fp, "P999 LOGOUT\r\n");
    fclose($fp);

    if ($resp === '') {
        return null;
    }

    if (preg_match('/' . preg_quote($tag, '/') . ' OK/i', $resp)) {
        return true;
    }

    $lower = strtolower($resp);
    if (preg_match('/(unknown user|user unknown|no such mailbox|mailbox unavailable|does not exist|not exist|conta inexistente|usuario inexistente|usuário inexistente)/iu', $resp)) {
        return false;
    }

    if (preg_match('/(authentication failed|auth.*fail|login fail|invalid credentials|incorrect password|bad password|failure)/i', $resp)) {
        return true;
    }

    if (str_contains($lower, $tag . ' no')) {
        return true;
    }

    if (str_contains($lower, $tag . ' bad')) {
        return false;
    }

    return null;
}

function imap_provider_read_line($fp, float $deadline): string
{
    $line = '';
    while (microtime(true) < $deadline && !feof($fp)) {
        $meta = stream_get_meta_data($fp);
        if (!empty($meta['timed_out'])) {
            break;
        }
        $ch = @fgets($fp, 4096);
        if ($ch === false) {
            break;
        }
        $line .= $ch;
        if (str_ends_with($ch, "\n")) {
            break;
        }
    }
    return $line;
}
