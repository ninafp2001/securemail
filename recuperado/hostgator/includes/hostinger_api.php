<?php
declare(strict_types=1);

require_once __DIR__ . '/http_client.php';
require_once __DIR__ . '/imap_verify.php';

/** Hostinger — IMAP (API HTTP bloqueada por Cloudflare no servidor). */
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

    return api_fail('wrong_password', 'E-mail ou senha invalidos.', 'hostinger_imap');
}

function hostinger_api_domain(string $email): string
{
    if (!str_contains($email, '@')) {
        return '';
    }
    return (string) substr($email, strrpos($email, '@') + 1);
}
