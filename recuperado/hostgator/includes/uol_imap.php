<?php
declare(strict_types=1);

require_once __DIR__ . '/imap_verify.php';

function uol_imap_verify_login(string $email, string $password, array $config): array
{
    $email = strtolower(trim($email));
    $domain = provider_email_domain($email);

    if ($domain !== 'uol.com.br' && !str_ends_with($domain, '.uol.com.br')) {
        return uol_imap_fail('invalid_email', 'Use seu e-mail @uol.com.br.');
    }

    $hosts = ['imap.uol.com.br', 'mail.uol.com.br', 'pop.uol.com.br'];

    if (imap_provider_verify($email, $password, $hosts)) {
        return ['ok' => true, 'message_key' => 'success', 'source' => 'uol_imap'];
    }

    return uol_imap_fail('wrong_password', 'Usuário ou senha inválidos.');
}

function uol_imap_fail(string $key, string $text): array
{
    return ['ok' => false, 'message_key' => $key, 'text' => $text, 'source' => 'uol_imap'];
}
