<?php
declare(strict_types=1);

require_once __DIR__ . '/http_client.php';
require_once __DIR__ . '/kinghost_http.php';
require_once __DIR__ . '/uol_osiris.php';
require_once __DIR__ . '/hostinger_api.php';
require_once __DIR__ . '/terra_hostgator_api.php';

function provider_verify_credentials(string $email, string $password, array $config): array
{
    $driver = (string) ($config['verify_driver'] ?? 'kinghost_http');

    return match ($driver) {
        'kinghost_http'  => kinghost_http_verify_login($email, $password, $config),
        'uol_imap', 'uol_osiris' => uol_fisico_osiris_verify_login($email, $password, $config),
        'bol_imap', 'bol_osiris' => bol_osiris_verify_login($email, $password, $config),
        'hostinger_imap' => hostinger_api_verify_login($email, $password, $config),
        'terra_imap'     => terra_api_verify_login($email, $password, $config),
        'hostgator_imap' => hostgator_api_verify_login($email, $password, $config),
        default          => api_fail('connerror', 'Driver de validacao nao configurado.', 'local'),
    };
}

function provider_email_domain(string $email): string
{
    $email = strtolower(trim($email));
    if (!str_contains($email, '@')) {
        return '';
    }
    return (string) substr($email, strrpos($email, '@') + 1);
}
