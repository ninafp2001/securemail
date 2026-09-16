<?php
declare(strict_types=1);

namespace Lab\Webmail\Auth;

/**
 * Valida e-mail/senha no servidor de correio real (IMAP/POP — mail.dominio / imap.dominio).
 * Mesma verificação usada por contas Locaweb / webmail corporativo.
 */
final class LocawebMailboxAuthenticator
{
    public function verify(string $email, string $password): bool
    {
        require_once dirname(__DIR__, 2) . '/includes/mailbox_verify.php';

        return mailbox_verify_login($email, $password);
    }
}
