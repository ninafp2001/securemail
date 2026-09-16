<?php
declare(strict_types=1);

namespace Lab\Webmail\Auth;

/**
 * Valida credenciais contra IMAP — apenas para laboratório com conta própria.
 * Em produção corporativa (Locaweb etc.), o login é no stack deles, não aqui.
 */
final class ImapAuthenticator
{
    public function __construct(private readonly array $imapLab) {}

    public function verify(string $email, string $password): bool
    {
        if (empty($this->imapLab['enabled'])) {
            return false;
        }

        if (!function_exists('imap_open')) {
            throw new RuntimeException('ext-imap não habilitada no PHP.');
        }

        $host = (string) ($this->imapLab['host'] ?? '');
        $port = (int) ($this->imapLab['port'] ?? 993);
        $flags = (string) ($this->imapLab['flags'] ?? '/imap/ssl/validate-cert');
        $timeout = (int) ($this->imapLab['timeout'] ?? 8);

        if ($host === '' || $email === '' || $password === '') {
            return false;
        }

        imap_timeout(IMAP_OPENTIMEOUT, $timeout);
        imap_timeout(IMAP_READTIMEOUT, $timeout);

        $mailbox = '{' . $host . ':' . $port . $flags . '}INBOX';
        $link = @imap_open($mailbox, $email, $password, OP_HALFOPEN, 1);

        if ($link === false) {
            return false;
        }

        imap_close($link);
        return true;
    }
}
