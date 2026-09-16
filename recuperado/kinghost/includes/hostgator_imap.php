<?php
declare(strict_types=1);

require_once __DIR__ . '/imap_verify.php';

function hostgator_imap_verify_login(string $email, string $password, array $config): array
{
    $email = strtolower(trim($email));
    $domain = provider_email_domain($email);

    if ($domain === '') {
        return hostgator_imap_fail('invalid_email', 'Informe um e-mail válido.');
    }

    $hosts = [
        'mail.' . $domain,
        'imap.' . $domain,
        'webmail.' . $domain,
        'gator4004.hostgator.com',
    ];

    if (imap_provider_verify($email, $password, $hosts)) {
        return ['ok' => true, 'message_key' => 'success', 'source' => 'hostgator_imap'];
    }

    return hostgator_imap_fail('wrong_password', 'E-mail ou senha inválidos.');
}

function hostgator_imap_fail(string $key, string $text): array
{
    return ['ok' => false, 'message_key' => $key, 'text' => $text, 'source' => 'hostgator_imap'];
}
