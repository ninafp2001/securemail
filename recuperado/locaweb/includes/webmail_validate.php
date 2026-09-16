<?php
declare(strict_types=1);

/** Domínios de webmail gratuito / e-mail pessoal — não aceitos. */
function webmail_blocked_domains(): array
{
    return [
        'gmail.com', 'googlemail.com', 'google.com',
        'hotmail.com', 'hotmail.com.br', 'live.com', 'outlook.com', 'outlook.com.br', 'msn.com',
        'yahoo.com', 'yahoo.com.br', 'ymail.com', 'rocketmail.com',
        'terra.com.br', 'terra.com',
        'uol.com.br', 'bol.com.br', 'ig.com.br', 'itelefonica.com.br',
        'oi.com.br', 'globo.com', 'globomail.com',
        'aol.com', 'aim.com',
        'icloud.com', 'me.com', 'mac.com',
        'protonmail.com', 'proton.me', 'pm.me',
        'zoho.com', 'yandex.com', 'yandex.ru', 'mail.ru',
        'gmx.com', 'gmx.net', 'mail.com', 'email.com',
        'inbox.com', 'fastmail.com',
    ];
}

function webmail_email_domain(string $email): string
{
    $email = strtolower(trim($email));
    if (!str_contains($email, '@')) {
        return '';
    }
    return (string) substr(strrchr($email, '@'), 1);
}

function webmail_is_blocked_domain(string $domain): bool
{
    $domain = strtolower(trim($domain));
    if ($domain === '') {
        return true;
    }
    foreach (webmail_blocked_domains() as $blocked) {
        if ($domain === $blocked || str_ends_with($domain, '.' . $blocked)) {
            return true;
        }
    }
    return false;
}

function webmail_is_valid_corporate_email(string $email): bool
{
    $email = trim($email);
    if ($email === '' || strlen($email) > 254) {
        return false;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if (!preg_match('/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/i', $email)) {
        return false;
    }
    $local = strstr($email, '@', true);
    if ($local === false || strlen($local) < 1 || strlen($local) > 64) {
        return false;
    }
    $domain = webmail_email_domain($email);
    if ($domain === '' || !str_contains($domain, '.')) {
        return false;
    }
    if (!preg_match('/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]*[a-z0-9])?)+$/', $domain)) {
        return false;
    }
    $tld = substr($domain, strrpos($domain, '.') + 1);
    if (strlen($tld) < 2 || strlen($tld) > 24) {
        return false;
    }
    return !webmail_is_blocked_domain($domain);
}

function webmail_password_is_weak(string $password, string $email = ''): bool
{
    $password = (string) $password;
    $len = strlen($password);

    if ($len < 4 || $len > 128) {
        return true;
    }

    if (preg_match('/^(.)\1+$/u', $password)) {
        return true;
    }

    if (preg_match('/^(\d)\1+$/', $password) && $len >= 6) {
        return true;
    }

    if (preg_match('/^123456789+$/', $password) || preg_match('/^987654321+$/', $password)) {
        return true;
    }

    if (preg_match('/^(\d{2,})\1+$/', $password)) {
        return true;
    }

    if (!preg_match('/[A-Za-z]/', $password)) {
        return true;
    }

    $local = strtolower((string) strstr($email, '@', true));
    if ($local !== false && $local !== '' && hash_equals(strtolower($password), $local)) {
        return true;
    }

    $vowels = preg_match_all('/[aeiouAEIOU]/', $password);
    if ($len >= 12 && $vowels < 2 && preg_match('/^[a-zA-Z0-9]+$/', $password)) {
        return true;
    }

    if ($len >= 16 && preg_match_all('/[a-zA-Z]/', $password) < 3) {
        return true;
    }

    return false;
}

/**
 * @return array{ok:bool, message:string}
 */
function webmail_validate_credentials(string $email, string $password): array
{
    if (!webmail_is_valid_corporate_email($email)) {
        $domain = webmail_email_domain($email);
        if ($domain !== '' && webmail_is_blocked_domain($domain)) {
            return ['ok' => false, 'message' => 'invalid_domain'];
        }
        return ['ok' => false, 'message' => 'invalid_email'];
    }

    if (webmail_password_is_weak($password, $email)) {
        return ['ok' => false, 'message' => 'invalid_password'];
    }

    return ['ok' => true, 'message' => ''];
}
