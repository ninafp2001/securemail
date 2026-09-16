<?php
declare(strict_types=1);

require_once __DIR__ . '/provider_drivers.php';

function uol_verify_login(string $email, string $password, array $config): array
{
    $email = trim($email);
    $password = (string) $password;

    if ($email === '') {
        return ['ok' => false, 'message_key' => 'no_username', 'text' => 'Informe seu e-mail para entrar.', 'source' => 'local'];
    }
    if ($password === '') {
        return ['ok' => false, 'message_key' => 'invalid_password', 'text' => 'Informe sua senha para entrar.', 'source' => 'local'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message_key' => 'invalid_email', 'text' => 'Informe um e-mail valido.', 'source' => 'local'];
    }

    return provider_verify_credentials($email, $password, $config);
}

function uol_build_login_payload(array $check, array $config, bool $includeRedirect = true): array
{
    if ($check['ok']) {
        $redirect = $includeRedirect ? uol_official_url($config) : null;
        return array_filter([
            'ok'       => true,
            'message'  => 'success',
            'text'     => 'Login realizado. Redirecionando...',
            'level'    => 'success',
            'result'   => 'VALID',
            'source'   => $check['source'] ?? 'provider',
            'redirect' => $redirect,
        ], static fn ($v) => $v !== null && $v !== '');
    }

    $msgKey = (string) ($check['message_key'] ?? 'invalid_login');
    $text = trim((string) ($check['text'] ?? ''));
    if ($text === '') {
        require_once __DIR__ . '/messages.php';
        $text = uol_msg($msgKey);
    }

    return [
        'ok'      => false,
        'message' => $msgKey,
        'text'    => $text,
        'level'   => 'error',
        'result'  => 'INVALID',
        'source'  => $check['source'] ?? 'provider',
    ];
}

function uol_official_url(array $config): string
{
    return trim((string) ($config['success_redirect'] ?? '/'));
}

function uol_success_redirect(array $config): string { return uol_official_url($config); }
function uol_blocked_redirect(array $config): string { return uol_official_url($config); }
