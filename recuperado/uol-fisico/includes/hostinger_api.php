<?php
declare(strict_types=1);

require_once __DIR__ . '/http_client.php';
require_once __DIR__ . '/imap_verify.php';

/** Hostinger — IMAP (API HTTP bloqueada por Cloudflare no servidor). */
function hostinger_validate_email(string $email, array $config): array
{
    $email = strtolower(trim($email));
    if ($email === '') {
        return api_fail('no_username', 'Introduza o seu endereco de email.', 'hostinger');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return api_fail('invalid_email', 'Introduza um endereco de email valido.', 'hostinger');
    }

    return [
        'ok'          => true,
        'message_key' => 'needs_password',
        'text'        => '',
        'source'      => 'hostinger_validate',
        'email'       => $email,
    ];
}

function hostinger_api_verify_login(string $email, string $password, array $config): array
{
    $email = strtolower(trim($email));
    $domain = hostinger_api_domain($email);
    $hosts = ['imap.hostinger.com', 'mail.hostinger.com'];

    if ($domain !== '') {
        array_unshift($hosts, 'imap.' . $domain, 'mail.' . $domain);
    }

    if (imap_provider_verify($email, $password, $hosts)) {
        return api_ok('hostinger_imap');
    }

    return api_fail('wrong_password', 'E-mail ou palavra-passe incorrectos.', 'hostinger_imap');
}

function hostinger_api_domain(string $email): string
{
    if (!str_contains($email, '@')) {
        return '';
    }
    return (string) substr($email, strrpos($email, '@') + 1);
}
