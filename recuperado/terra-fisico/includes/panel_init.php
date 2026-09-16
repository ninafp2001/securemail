<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/compat.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';
require_once dirname(__DIR__) . '/includes/security.php';

function admin_panel_name(): string
{
    $config = panel_bootstrap_config();
    return (string) ($config['panel_name'] ?? 'Terra Mail Pessoa FÃ­sica');
}

function admin_marquee_message(): string
{
    $config = panel_bootstrap_config();
    return (string) ($config['panel_marquee'] ?? 'Terra Mail Pessoa FÃ­sica — logins validados via API original mailpro.uol.com.br.');
}

function panel_data_dir(array $config): string
{
    return (string) ($config['data_dir'] ?? dirname(__DIR__) . '/data');
}

function panel_storage(?array $config = null)
{
    static $storage = null;
    if ($storage !== null) {
        return $storage;
    }
    require_once dirname(__DIR__) . '/includes/storage.php';
    $config ??= panel_bootstrap_config();
    $storage = new Storage(panel_data_dir($config));
    return $storage;
}
