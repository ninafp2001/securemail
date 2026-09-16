<?php
declare(strict_types=1);

namespace Lab\Webmail\Auth;

use Lab\Webmail\Security\SessionManager;

/**
 * Modo laboratório: sem IMAP, aceita apenas usuário demo configurado localmente.
 * Com IMAP habilitado, valida no servidor de teste.
 */
final class LoginService
{
    private const DEMO_USER = 'aluno@lab.local';
    private const DEMO_PASS = 'LabSenhaForte123!';

    public function __construct(
        private readonly ?ImapAuthenticator $imap,
        private readonly bool $imapEnabled,
        private readonly ?CredentialList $localList,
        private readonly bool $allowLocalList,
        private readonly ?LocawebMailboxAuthenticator $mailbox,
        private readonly bool $mailboxVerify,
    ) {}

    public function attempt(string $email, string $password): bool
    {
        $email = strtolower(trim($email));

        if ($this->mailboxVerify && $this->mailbox !== null) {
            return $this->mailbox->verify($email, $password);
        }

        if ($this->imapEnabled && $this->imap !== null) {
            return $this->imap->verify($email, $password);
        }

        if ($this->allowLocalList && $this->localList !== null && $this->localList->verify($email, $password)) {
            return true;
        }

        return hash_equals(self::DEMO_USER, $email)
            && hash_equals(self::DEMO_PASS, $password);
    }

    public function establishSession(string $email): void
    {
        SessionManager::regenerate();
        $_SESSION['user'] = $email;
        $_SESSION['auth_at'] = time();
    }
}
