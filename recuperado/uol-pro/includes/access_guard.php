<?php
declare(strict_types=1);

/** Anti-bot + geo BR + limite de visitas por IP (página pública). */
function enforce_public_access(array $config): void
{
    require_once __DIR__ . '/anti_bot.php';
    anti_bot_enforce($config);
    enforce_ip_page_limit($config);
}

function enforce_ip_page_limit(array $config): void
{
    require_once __DIR__ . '/storage.php';
    require_once __DIR__ . '/uol_verify.php';

    $storage = new Storage($config['data_dir']);
    $ip = client_ip();
    $max = (int) ($config['max_ip_visits'] ?? 4);
    $redirect = uol_blocked_redirect($config);

    if (!$storage->trackIpPageHit($ip, $max)) {
        header('Location: ' . $redirect, true, 302);
        exit;
    }
}
