<?php
declare(strict_types=1);

use Lab\Webmail\Auth\CredentialList;
use Lab\Webmail\Auth\ImapAuthenticator;
use Lab\Webmail\Auth\LocawebMailboxAuthenticator;
use Lab\Webmail\Auth\LoginService;

function make_login_service(array $config): LoginService
{
    $imapCfg = $config['imap_lab'] ?? [];
    $imapEnabled = !empty($imapCfg['enabled']);
    $imap = $imapEnabled ? new ImapAuthenticator($imapCfg) : null;

    $env = (string) ($config['env'] ?? 'local');
    $listFile = (string) ($config['credentials_list'] ?? app_path('listas.txt'));
    $allowList = $env === 'local' && !empty($config['allow_credentials_list']);
    $list = $allowList ? new CredentialList($listFile) : null;

    $mailboxVerify = array_key_exists('mailbox_verify', $config)
        ? (bool) $config['mailbox_verify']
        : $env !== 'local';
    $mailbox = $mailboxVerify ? new LocawebMailboxAuthenticator() : null;

    return new LoginService($imap, $imapEnabled, $list, $allowList, $mailbox, $mailboxVerify);
}

/**
 * @return list<array{email: string, ok: bool}>
 */
function test_credentials_file(LoginService $service, string $filePath): array
{
    $out = [];
    if (!is_readable($filePath)) {
        return $out;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $out;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $sep = strpos($line, ':');
        if ($sep === false) {
            continue;
        }
        $email = trim(substr($line, 0, $sep));
        $pass = substr($line, $sep + 1);
        if ($email === '') {
            continue;
        }

        try {
            $ok = $service->attempt($email, $pass);
        } catch (Throwable $e) {
            $ok = false;
        }

        $out[] = ['email' => $email, 'ok' => $ok];
    }

    return $out;
}
