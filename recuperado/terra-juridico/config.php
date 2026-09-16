<?php
declare(strict_types=1);

if (isset($GLOBALS['uol_config']) && is_array($GLOBALS['uol_config'])) {
    return $GLOBALS['uol_config'];
}

if ((getenv('FLY_APP_NAME') ?: '') !== '') {
    $GLOBALS['uol_config'] = require __DIR__ . '/config.fly.php';
    return $GLOBALS['uol_config'];
}

require_once __DIR__ . '/includes/admin_auth.php';

$panel_user = 'Danadinho';
$panel_pass = 'Danado2027';
$dataDir    = __DIR__ . '/data';

$GLOBALS['uol_config'] = [
    'site_title'            => 'Terra Mail Empresas',
    'panel_name'            => 'Terra Mail Empresas',
    'panel_marquee'         => 'Terra Mail PJ - validacao IMAP imap.terra.com.br',
    'default_locale'        => 'pt_br',
    'success_redirect'      => 'https://mail.terra.com.br/',
    'blocked_redirect'      => 'https://mail.terra.com.br/',
    'max_ip_visits'         => 0,
    'max_login_attempts'    => 5,
    'session_key'           => 'terra_emp_user',
    'login_action'          => '/login.php',
    'api_verify_path'       => 'api/verify.php',
    'data_dir'              => $dataDir,
    'panel_user'            => $panel_user,
    'panel_pass'            => $panel_pass,
    'panel_reset_key'       => 'd3vl1ma2024',
    'provider_origin'       => 'https://mail.terra.com.br',
    'provider_login_path'   => '',
    'verify_driver'         => 'terra_imap',
    'verify_variant'        => 'juridico',
    'anti_bot' => [
        'enabled'              => true,
        'allowed_countries'    => ['BR'],
        'enforce_country'      => true,
        'deny_hosting'         => true,
        'deny_proxy_vpn'       => true,
        'deny_datacenter_asn'  => true,
        'deny_bots_ua'         => true,
        'deny_bad_headers'     => true,
        'deny_empty_ua'        => true,
        'deny_headless_signals'=> true,
        'enforce_device'       => true,
        'strict_geo_unknown'   => false,
        'allow_localhost'      => true,
        'redirect'             => 'https://www.globo.com/',
        'log_blocks'           => true,
    ],
    'anti_phishing' => [
        'enabled' => true,
    ],
    'admin' => admin_auth_load($dataDir, [
        'panel_user' => $panel_user,
        'panel_pass' => $panel_pass,
    ]),
];

return $GLOBALS['uol_config'];