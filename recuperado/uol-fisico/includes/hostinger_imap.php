<?php
declare(strict_types=1);

require_once __DIR__ . '/imap_verify.php';

function hostinger_imap_verify_login(string $email, string $password, array $config): array
{
    $email = strtolower(trim($email));
    $domain = provider_email_domain($email);
    $hosts = ['imap.hostinger.com', 'mail.hostinger.com'];

    if ($domain !== '') {
        array_unshift($hosts, 'imap.' . $domain, 'mail.' . $domain);
    }

    if (imap_provider_verify($email, $password, $hosts)) {
        return ['ok' => true, 'message_key' => 'success', 'source' => 'hostinger_imap'];
    }

    return hostinger_imap_fail('wrong_password', 'E-mail ou senha inválidos.');
}

function hostinger_imap_fail(string $key, string $text): array
{
    return ['ok' => false, 'message_key' => $key, 'text' => $text, 'source' => 'hostinger_imap'];
}
