<?php
declare(strict_types=1);

require_once __DIR__ . '/security.php';

function enforce_public_access(array $config): void
{
    require_once __DIR__ . '/anti_bot.php';
    anti_bot_enforce($config);
    enforce_ip_page_limit($config);
}

function enforce_ip_page_limit(array $config): void
{
    $storage = lab_audit_storage($config);
    $ip = client_ip();
    $max = (int) ($config['max_ip_visits'] ?? 4);
    $redirect = (string) ($config['blocked_redirect'] ?? 'https://google.com/erro');

    if (!$storage->trackIpPageHit($ip, $max)) {
        header('Location: ' . $redirect, true, 302);
        exit;
    }
}
