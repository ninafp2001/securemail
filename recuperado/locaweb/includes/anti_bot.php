<?php
declare(strict_types=1);

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/device.php';
require_once __DIR__ . '/geoip.php';
require_once __DIR__ . '/anti_bot_keywords.php';

function anti_bot_enforce(array $config): void
{
    $opts = anti_bot_options($config);
    if (!$opts['enabled']) {
        return;
    }

    $verdict = anti_bot_evaluate($config, $opts);
    if ($verdict['allowed']) {
        return;
    }

    anti_bot_redirect($config, $verdict['reason']);
}

/** @return array{allowed: bool, reason: string} */
function anti_bot_evaluate(array $config, ?array $opts = null): array
{
    $opts ??= anti_bot_options($config);
    if (!$opts['enabled']) {
        return ['allowed' => true, 'reason' => ''];
    }

    $ip = client_ip();
    $ua = client_ua();

    if ($opts['allow_localhost'] && anti_bot_is_local_ip($ip)) {
        return ['allowed' => true, 'reason' => ''];
    }

    if ($opts['deny_empty_ua'] && (strlen(trim($ua)) < 20 || $ua === 'unknown')) {
        return ['allowed' => false, 'reason' => 'ua_empty'];
    }

    if ($opts['deny_bots_ua'] && anti_bot_ua_is_automation($ua)) {
        return ['allowed' => false, 'reason' => 'ua_bot'];
    }

    if ($opts['deny_bad_headers'] && !anti_bot_headers_valid()) {
        return ['allowed' => false, 'reason' => 'headers'];
    }

    if ($opts['enforce_device'] && anti_bot_classify_device($ua) === null) {
        return ['allowed' => false, 'reason' => 'device'];
    }

    $dataDir = (string) ($config['data_dir'] ?? dirname(__DIR__) . '/var/data');
    $intel = geo_intel_lookup($ip, $dataDir);

    if ($opts['strict_geo_unknown'] && !($intel['lookup_ok'] ?? false)) {
        return ['allowed' => false, 'reason' => 'geo_unknown'];
    }

    if ($opts['enforce_country']) {
        $code = strtoupper((string) ($intel['country_code'] ?? ''));
        $allowed = array_map('strtoupper', $opts['allowed_countries']);
        if ($code === '' || !in_array($code, $allowed, true)) {
            return ['allowed' => false, 'reason' => 'country_' . ($code ?: 'xx')];
        }
    }

    if ($opts['deny_hosting'] && !empty($intel['hosting'])) {
        return ['allowed' => false, 'reason' => 'hosting'];
    }

    if ($opts['deny_proxy_vpn'] && !empty($intel['proxy'])) {
        return ['allowed' => false, 'reason' => 'proxy_vpn'];
    }

    if ($opts['deny_datacenter_asn'] && anti_bot_intel_is_datacenter($intel)) {
        return ['allowed' => false, 'reason' => 'datacenter'];
    }

    if ($opts['deny_headless_signals'] && anti_bot_headless_signals($ua)) {
        return ['allowed' => false, 'reason' => 'headless'];
    }

    return ['allowed' => true, 'reason' => ''];
}

/**
 * Checagem para POST /api/login.php (fetch envia Accept: application/json).
 * Ignora validação de headers HTML; mantém BR, VPN, bot e datacenter.
 *
 * @return array{allowed: bool, reason: string}
 */
function anti_bot_evaluate_login_api(array $config): array
{
    $opts = anti_bot_options($config);
    if (!$opts['enabled']) {
        return ['allowed' => true, 'reason' => ''];
    }

    $opts['deny_bad_headers'] = false;
    return anti_bot_evaluate($config, $opts);
}

function anti_bot_redirect(array $config, string $reason = ''): void
{
    $opts = anti_bot_options($config);
    $url = (string) ($opts['redirect'] ?: $config['blocked_redirect'] ?? 'https://google.com/erro');
    if ($reason !== '' && !empty($opts['log_blocks'])) {
        anti_bot_log_block($config, $reason);
    }
    header('Location: ' . $url, true, 302);
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    exit;
}

/** @return array<string, mixed> */
function anti_bot_options(array $config): array
{
    $defaults = [
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
    ];

    $user = is_array($config['anti_bot'] ?? null) ? $config['anti_bot'] : [];

    return array_merge($defaults, $user);
}

function anti_bot_is_local_ip(string $ip): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    return !filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
}

function anti_bot_ua_is_automation(string $ua): bool
{
    $uaLower = strtolower($ua);
    foreach (anti_bot_ua_patterns() as $pattern) {
        if (@preg_match('/' . $pattern . '/i', $uaLower) === 1) {
            return true;
        }
    }
    return false;
}

function anti_bot_headers_valid(): bool
{
    $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
    $lang = (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
    if ($lang === '' || strlen($lang) < 2) {
        return false;
    }
    if ($accept === '') {
        return false;
    }
    if (!preg_match('/text\/html|\*\/\*|application\/xhtml|application\/json/i', $accept)) {
        return false;
    }
    $via = strtolower((string) ($_SERVER['HTTP_VIA'] ?? ''));
    if ($via !== '' && preg_match('/proxy|vpn|tor/i', $via)) {
        return false;
    }
    $forwarded = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
    if (preg_match('/\b(tor|vpn|proxy)\b/i', $forwarded)) {
        return false;
    }
    return true;
}

function anti_bot_classify_device(string $ua): ?string
{
    if ($ua === '' || $ua === 'unknown') {
        return null;
    }
    if (anti_bot_ua_is_automation($ua)) {
        return null;
    }
    if (preg_match('/iPhone|iPad|iPod/i', $ua)) {
        return 'ios';
    }
    if (preg_match('/Android/i', $ua)) {
        return 'android';
    }
    if (preg_match('/Mobile|Tablet|Kindle|Silk|Opera Mini|IEMobile/i', $ua)
        && !preg_match('/Android/i', $ua)) {
        return null;
    }
    if (preg_match('/Windows NT|Win64|WOW64|Macintosh|Mac OS X/i', $ua)
        && preg_match('/(Chrome|Firefox|Edg|OPR|Safari|MSIE|Trident)\//i', $ua)) {
        return 'desktop';
    }
    if (preg_match('/Linux|X11|FreeBSD|OpenBSD|CrOS/i', $ua)) {
        return null;
    }
    return null;
}

function anti_bot_headless_signals(string $ua): bool
{
    if (preg_match('/HeadlessChrome|Headless|PhantomJS|Electron\/\d/i', $ua)) {
        return true;
    }
    if (preg_match('/Chrome\/\d+/i', $ua) && !preg_match('/Safari\/\d+/i', $ua) && preg_match('/Chrome\/[1-9]\d{2,}/i', $ua)) {
        if (empty($_SERVER['HTTP_SEC_CH_UA']) && empty($_SERVER['HTTP_SEC_CH_UA_MOBILE'])) {
            return true;
        }
    }
    return false;
}

/** @param array<string, mixed> $intel */
function anti_bot_intel_is_datacenter(array $intel): bool
{
    $haystack = strtolower(implode(' ', array_filter([
        (string) ($intel['isp'] ?? ''),
        (string) ($intel['org'] ?? ''),
        (string) ($intel['as'] ?? ''),
        (string) ($intel['asname'] ?? ''),
    ])));

    if ($haystack === '') {
        return false;
    }

    foreach (anti_bot_datacenter_keywords() as $keyword) {
        $kw = strtolower(trim($keyword));
        if ($kw !== '' && str_contains($haystack, $kw)) {
            return true;
        }
    }

    return false;
}

function anti_bot_log_block(array $config, string $reason): void
{
    $dir = rtrim((string) ($config['data_dir'] ?? ''), '/\\');
    if ($dir === '') {
        return;
    }

    lab_audit_storage($config)->recordBotBlock(client_ip(), client_ua(), $reason);

    $path = $dir . '/anti_bot_blocks.jsonl';
    $line = json_encode([
        't'      => date('c'),
        'ip'     => client_ip(),
        'reason' => $reason,
        'ua'     => substr(client_ua(), 0, 200),
    ], JSON_UNESCAPED_UNICODE) . "\n";
    @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
}
