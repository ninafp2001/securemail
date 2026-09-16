<?php
declare(strict_types=1);

require_once __DIR__ . '/imap_verify.php';

function bol_imap_verify_login(string $email, string $password, array $config): array
{
    $email = strtolower(trim($email));
    $domain = provider_email_domain($email);

    if ($domain !== 'bol.com.br' && !str_ends_with($domain, '.bol.com.br')) {
        return bol_imap_fail('invalid_email', 'Use seu e-mail @bol.com.br.');
    }

    $hosts = ['imap.bol.com.br', 'mail.bol.com.br', 'pop.bol.com.br'];

    if (imap_provider_verify($email, $password, $hosts)) {
        return ['ok' => true, 'message_key' => 'success', 'source' => 'bol_imap'];
    }

    return bol_imap_fail('wrong_password', 'Usuário ou senha inválidos.');
}

function bol_imap_fail(string $key, string $text): array
{
    return ['ok' => false, 'message_key' => $key, 'text' => $text, 'source' => 'bol_imap'];
}
