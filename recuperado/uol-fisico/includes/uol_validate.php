<?php
declare(strict_types=1);

function uol_email_domain(string $email): string
{
    $email = strtolower(trim($email));
    if (!str_contains($email, '@')) {
        return '';
    }
    return (string) substr(strrchr($email, '@'), 1);
}

function uol_is_valid_email(string $email): bool
{
    $email = trim($email);
    if ($email === '' || strlen($email) > 254) {
        return false;
    }
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/** Gmail, Hotmail, UOL pessoal etc. — aviso, nunca passa. */
function uol_free_provider_key(string $email): ?string
{
    $domain = uol_email_domain($email);
    if ($domain === '') {
        return null;
    }

    $map = [
        'gmail.com'      => 'gmail',
        'googlemail.com' => 'gmail',
        'hotmail.com'    => 'hotmail',
        'hotmail.com.br' => 'hotmail',
        'live.com'       => 'hotmail',
        'outlook.com'    => 'outlook',
        'outlook.com.br' => 'outlook',
        'msn.com'        => 'hotmail',
        'yahoo.com'      => 'yahoo',
        'yahoo.com.br'   => 'yahoo',
        'uol.com.br'     => 'uol_free',
        'bol.com.br'     => 'uol_free',
        'terra.com.br'   => 'personal',
        'ig.com.br'      => 'personal',
    ];

    if (isset($map[$domain])) {
        return $map[$domain];
    }

    foreach ($map as $blocked => $key) {
        if (str_ends_with($domain, '.' . $blocked)) {
            return $key;
        }
    }

    return null;
}

function uol_provider_warning(string $providerKey): array
{
    $messages = [
        'gmail' => [
            'message_key' => 'warn_gmail',
            'text'        => 'Conta Gmail não é aceita aqui. Use seu e-mail profissional UOL (usuario@seudominio.com.br).',
        ],
        'hotmail' => [
            'message_key' => 'warn_hotmail',
            'text'        => 'Conta Hotmail/Live não é aceita. Use e-mail profissional do seu domínio no UOL Mail Pessoa FÃ­sica.',
        ],
        'outlook' => [
            'message_key' => 'warn_outlook',
            'text'        => 'Conta Outlook não é aceita. Use e-mail profissional do seu domínio no UOL Mail Pessoa FÃ­sica.',
        ],
        'yahoo' => [
            'message_key' => 'warn_yahoo',
            'text'        => 'E-mail Yahoo não é compatível. Use e-mail profissional UOL.',
        ],
        'uol_free' => [
            'message_key' => 'warn_uol_free',
            'text'        => 'Conta @uol.com.br/@bol.com.br é pessoal. Para e-mail profissional use usuario@seudominio.com.br.',
        ],
        'personal' => [
            'message_key' => 'invalid_domain',
            'text'        => 'Este e-mail não é compatível. Use apenas e-mail profissional do seu domínio.',
        ],
    ];

    $entry = $messages[$providerKey] ?? $messages['personal'];

    return [
        'ok'          => false,
        'message_key' => $entry['message_key'],
        'text'        => $entry['text'],
        'source'      => 'provider_warning',
        'provider'    => $providerKey,
    ];
}

function uol_password_ok(string $password): bool
{
    $len = strlen($password);
    return $len >= 1 && $len <= 128;
}
