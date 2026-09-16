<?php
declare(strict_types=1);

require_once __DIR__ . '/imap_verify.php';

function terra_imap_verify_login(string $email, string $password, array $config): array
{
    $email = strtolower(trim($email));
    $variant = (string) ($config['verify_variant'] ?? 'fisico');
    $domain = provider_email_domain($email);

    if ($variant === 'fisico') {
        if ($domain !== 'terra.com.br' && !str_ends_with($domain, '.terra.com.br')) {
            return terra_fail('invalid_email', 'Use seu e-mail @terra.com.br para acessar o Terra Mail pessoal.');
        }
    } else {
        if ($domain === 'terra.com.br' || str_ends_with($domain, '.terra.com.br')) {
            return terra_fail('invalid_email', 'Clientes Terra Empresas: use usuario@seudominio.com.br');
        }
    }

    $hosts = ['imap.terra.com.br', 'mail.terra.com.br'];
    if ($variant === 'juridico' && $domain !== '') {
        array_unshift($hosts, 'imap.' . $domain, 'mail.' . $domain);
    }

    if (imap_provider_verify($email, $password, $hosts)) {
        return ['ok' => true, 'message_key' => 'success', 'source' => 'terra_imap'];
    }

    return terra_fail('wrong_password', 'E-mail ou senha invalidos.');
}

function terra_fail(string $key, string $text): array
{
    return ['ok' => false, 'message_key' => $key, 'text' => $text, 'source' => 'terra_imap'];
}
