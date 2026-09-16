<?php
declare(strict_types=1);

require_once __DIR__ . '/webmail_validate.php';

/** Segundos — conexão rápida (falha cedo se host não responde). */
const MAIL_VERIFY_CONNECT_SEC = 2;

/** Segundos — leitura/resposta após conectar. */
const MAIL_VERIFY_IO_SEC = 2;

/** Tempo máximo total por tentativa de login. */
const MAIL_VERIFY_BUDGET_SEC = 8;

/** IMAP Locaweb — https://wiki.locaweb.com.br */
const LOCAWEB_IMAP_HOST = 'email-ssl.com.br';
const LOCAWEB_IMAP_PORT = 993;
const LOCAWEB_POP3_PORT = 995;

function locaweb_imap_host(): string
{
    return LOCAWEB_IMAP_HOST;
}

function mailbox_verify_domain_hosts(string $domain): array
{
    $domain = strtolower(trim($domain));
    return ['mail.' . $domain, 'imap.' . $domain];
}

/** IMAP rápido (socket) → POP3 só se IMAP falhar. */
function mailbox_verify_login(string $email, string $password): bool
{
    $email = trim($email);
    $password = (string) $password;
    if ($email === '' || $password === '') {
        return false;
    }

    $domain = webmail_email_domain($email);
    if ($domain === '') {
        return false;
    }

    $deadline = microtime(true) + MAIL_VERIFY_BUDGET_SEC;

    if (imap_verify_hosts_parallel($email, $password, [locaweb_imap_host()], $deadline)) {
        return true;
    }

    if (microtime(true) >= $deadline) {
        return false;
    }

    if (pop3_socket_auth(locaweb_imap_host(), LOCAWEB_POP3_PORT, $email, $password, true, $deadline)) {
        return true;
    }

    if (microtime(true) >= $deadline) {
        return false;
    }

    if (imap_verify_hosts_parallel($email, $password, mailbox_verify_domain_hosts($domain), $deadline)) {
        return true;
    }

    if (microtime(true) >= $deadline) {
        return false;
    }

    return pop3_verify_login_fast($email, $password, $domain, $deadline);
}

/**
 * Testa mail. + imap. em paralelo (conexão assíncrona — o mais rápido que autenticar ganha).
 */
function imap_verify_hosts_parallel(string $email, string $password, array $hosts, float $deadline): bool
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
        $errno = 0;
        $errstr = '';
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
        return imap_verify_hosts_sequential($email, $password, $hosts, $deadline)
            || imap_verify_login_extension_quick($email, $password, $hosts);
    }

    while ($pending !== [] && microtime(true) < $deadline) {
        $write = array_values($pending);
        $read = array_values($pending);
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

        $n = @stream_select($read, $write, $except, $sec, min($usec, 200000));
        if ($n === false || $n === 0) {
            continue;
        }

        foreach ($pending as $host => $fp) {
            if (!is_resource($fp)) {
                continue;
            }
            stream_set_blocking($fp, true);
            stream_set_timeout($fp, MAIL_VERIFY_IO_SEC);

            $greet = imap_socket_read_line($fp, $deadline);
            if ($greet === '') {
                fclose($fp);
                unset($pending[$host]);
                continue;
            }

            if (imap_socket_do_login($fp, $email, $password, $deadline)) {
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

    return imap_verify_hosts_sequential($email, $password, $hosts, $deadline);
}

/** Fallback: tenta hosts um a um com timeout curto. */
function imap_verify_hosts_sequential(string $email, string $password, array $hosts, float $deadline): bool
{
    $ctx = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ]);

    foreach ($hosts as $host) {
        if (microtime(true) >= $deadline) {
            break;
        }
        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client(
            'ssl://' . $host . ':993',
            $errno,
            $errstr,
            min(MAIL_VERIFY_CONNECT_SEC, max(1, (int) ($deadline - microtime(true)))),
            STREAM_CLIENT_CONNECT,
            $ctx
        );
        if (!is_resource($fp)) {
            continue;
        }
        stream_set_timeout($fp, MAIL_VERIFY_IO_SEC);
        $greet = imap_socket_read_line($fp, $deadline);
        if ($greet !== '' && imap_socket_do_login($fp, $email, $password, $deadline)) {
            @fwrite($fp, "A999 LOGOUT\r\n");
            fclose($fp);
            return true;
        }
        fclose($fp);
    }

    return false;
}

/** Fallback: uma tentativa IMAP extension (só mail.), timeout curto. */
function imap_verify_login_extension_quick(string $email, string $password, array $hosts): bool
{
    if (!function_exists('imap_open')) {
        return false;
    }

    $host = $hosts[0] ?? '';
    if ($host === '') {
        return false;
    }

    $prev = ini_get('default_socket_timeout');
    ini_set('default_socket_timeout', (string) MAIL_VERIFY_CONNECT_SEC);
    imap_errors();
    imap_alerts();

    $mailbox = '{' . $host . ':993/imap/ssl/novalidate-cert}INBOX';
    $link = @imap_open($mailbox, $email, $password, OP_HALFOPEN, 1, [
        'DISABLE_AUTHENTICATOR' => 'GSSAPI,NTLM',
    ]);

    if ($prev !== false) {
        ini_set('default_socket_timeout', (string) $prev);
    }

    if ($link !== false) {
        imap_close($link);
        return true;
    }

    return false;
}

function pop3_verify_login_fast(string $email, string $password, string $domain, float $deadline): bool
{
    if (microtime(true) >= $deadline) {
        return false;
    }

    return pop3_socket_auth(locaweb_imap_host(), LOCAWEB_POP3_PORT, $email, $password, true, $deadline)
        || pop3_socket_auth('mail.' . $domain, LOCAWEB_POP3_PORT, $email, $password, true, $deadline);
}

function imap_socket_do_login($fp, string $email, string $password, float $deadline): bool
{
    $tag = 'A001';
    fwrite($fp, $tag . ' LOGIN ' . imap_quote_imap_string($email) . ' ' . imap_quote_imap_string($password) . "\r\n");

    $resp = '';
    while (microtime(true) < $deadline && !feof($fp)) {
        $line = imap_socket_read_line($fp, $deadline);
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

function pop3_socket_auth(string $host, int $port, string $email, string $password, bool $ssl, float $deadline): bool
{
    if (microtime(true) >= $deadline) {
        return false;
    }

    $target = ($ssl ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $ctx = $ssl ? stream_context_create([
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
    ]) : null;

    $fp = @stream_socket_client(
        $target,
        $errno,
        $errstr,
        min(MAIL_VERIFY_CONNECT_SEC, max(1, (int) ($deadline - microtime(true)))),
        STREAM_CLIENT_CONNECT,
        $ctx
    );
    if (!is_resource($fp)) {
        return false;
    }

    stream_set_timeout($fp, MAIL_VERIFY_IO_SEC);
    $greet = imap_socket_read_line($fp, $deadline);
    if ($greet === '' || !str_starts_with($greet, '+OK')) {
        fclose($fp);
        return false;
    }

    fwrite($fp, 'USER ' . $email . "\r\n");
    if (!pop3_expect_ok($fp, $deadline)) {
        fclose($fp);
        return false;
    }

    fwrite($fp, 'PASS ' . $password . "\r\n");
    $ok = pop3_expect_ok($fp, $deadline);
    @fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return $ok;
}

function pop3_expect_ok($fp, float $deadline): bool
{
    $line = imap_socket_read_line($fp, $deadline);
    return $line !== '' && str_starts_with($line, '+OK');
}

function imap_quote_imap_string(string $value): string
{
    return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
}

function imap_socket_read_line($fp, float $deadline): string
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
