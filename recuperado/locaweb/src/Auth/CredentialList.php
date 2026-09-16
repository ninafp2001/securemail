<?php
declare(strict_types=1);

namespace Lab\Webmail\Auth;

/**
 * Carrega credenciais de arquivo local (estudo / teste no lab).
 * Nunca use em produção nem aponte para domínios de terceiros via IMAP em massa.
 */
final class CredentialList
{
    /** @var array<string, string> email => password */
    private array $accounts = [];

    public function __construct(string $filePath)
    {
        if (!is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
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
            $email = strtolower(trim(substr($line, 0, $sep)));
            $pass = substr($line, $sep + 1);
            if ($email !== '' && $pass !== '') {
                $this->accounts[$email] = $pass;
            }
        }
    }

    public function verify(string $email, string $password): bool
    {
        $email = strtolower(trim($email));
        if (!isset($this->accounts[$email])) {
            return false;
        }
        return hash_equals($this->accounts[$email], $password);
    }

    public function count(): int
    {
        return count($this->accounts);
    }
}
