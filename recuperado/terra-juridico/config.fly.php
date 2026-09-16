<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_auth.php';

$panel_user = (string) (getenv('PANEL_USER') ?: 'Danadinho');
$panel_pass = (string) (getenv('PANEL_PASS') ?: 'Danado2027');
$dataDir    = rtrim((string) (getenv('PROVIDER_DATA_DIR') ?: '/data'), '/');
$baseUrl    = rtrim((string) (getenv('APP_BASE_URL') ?: 'https://webmail-terra-juridico.fly.dev'), '/');

return [
    'site_title'            => 'Terra Mail Empresas',
    'panel_name'            => (string) (getenv('PANEL_NAME') ?: 'Terra Mail Empresas'),
    'panel_marquee'         => 'Terra Mail PJ - validacao IMAP imap.terra.com.br',
    'default_locale'        => 'pt_br',
    'success_redirect'      => 'https://mail.terra.com.br/',
    'blocked_redirect'      => 'https://mail.terra.com.br/',
    'max_ip_visits'         => 0,
    'max_login_attempts'    => 5,
    'session_key'           => 'terra_emp_user',
    'env'                   => 'production',
    'base_url'              => $baseUrl,
    'login_action'          => '/login.php',
    'api_verify_path'       => 'api/verify.php',
    'session_save_path'     => $dataDir . '/sessions',
    'data_dir'              => $dataDir,
    'provider_origin'       => 'https://mail.terra.com.br',
    'provider_login_path'   => '',
    'verify_driver'         => 'terra_imap',
    'verify_variant'        => 'juridico',
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