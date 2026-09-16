<?php
declare(strict_types=1);

require_once __DIR__ . '/providers.php';

function unified_panel_config(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    $dataDir = '/data/painel';
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0775, true);
    }

    require_once '/app/providers/bol/includes/admin_auth.php';

    $panelUser = (string) (getenv('PANEL_USER') ?: 'danadinho');
    $panelPass = (string) (getenv('PANEL_PASS') ?: 'sorte');

    $cfg = [
        'panel_name'      => 'Cpanel Multiplo Webamil - Dev Danadinho V.3',
        'panel_marquee'   => 'Cpanel Multiplo Webamil - Dev Danadinho V.3',
        'data_dir'        => $dataDir,
        'panel_user'      => $panelUser,
        'panel_pass'      => $panelPass,
        'panel_reset_key' => (string) (getenv('PANEL_RESET_KEY') ?: 'fly-reset'),
        'admin'           => admin_auth_load($dataDir, [
            'panel_user' => $panelUser,
            'panel_pass' => $panelPass,
        ]),
    ];

    return $cfg;
}

function unified_provider_labels(): array
{
    return [
        'bol'            => 'BOL',
        'uol-fisico'     => 'UOL',
        'uol-pro'        => 'UOL Pro',
        'locaweb'        => 'Locaweb',
        'kinghost'       => 'KingHost',
        'hostinger'      => 'Hostinger',
        'hostgator'      => 'HostGator',
        'terra-fisico'   => 'Terra PF',
        'terra-juridico' => 'Terra PJ',
        'cpanel-webmail' => 'Webmail',
    ];
}

function provider_unified_data_dir(string $slug): string
{
    $dir = '/data/providers/' . $slug;
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir;
}

function unified_provider_data_dir(string $slug): string
{
    return provider_unified_data_dir($slug);
}

function unified_provider_storage(string $slug): ?object
{
    $dir = unified_provider_data_dir($slug);
    if (!is_dir($dir)) {
        return null;
    }

    if ($slug === 'locaweb') {
        require_once '/app/providers/locaweb/src/Audit/LabStorage.php';
        return new \Lab\Webmail\Audit\LabStorage($dir);
    }

    require_once '/app/providers/bol/includes/storage.php';
    return new Storage($dir);
}

function unified_all_storages(): array
{
    $out = [];
    foreach (array_keys(unified_provider_labels()) as $slug) {
        $storage = unified_provider_storage($slug);
        if ($storage !== null) {
            $out[$slug] = $storage;
        }
    }
    return $out;
}

function unified_merge_stats(array $statsList): array
{
    $keys = [
        'visits_today', 'visits_total', 'logins_today', 'logins_total',
        'unique_credentials', 'unique_ips', 'blocked_ips',
        'bots_today', 'bots_total',
        'iphone_today', 'iphone_total', 'android_today', 'android_total',
        'desktop_today', 'desktop_total',
    ];

    $merged = array_fill_keys($keys, 0);
    foreach ($statsList as $stats) {
        foreach ($keys as $key) {
            $merged[$key] += (int) ($stats[$key] ?? 0);
        }
    }

    return $merged;
}

function unified_provider_stats(): array
{
    $labels = unified_provider_labels();
    $result = [];

    foreach ($labels as $slug => $label) {
        $storage = unified_provider_storage($slug);
        $stats = $storage ? $storage->getStats() : [];
        $result[$slug] = [
            'slug'  => $slug,
            'label' => $label,
            'stats' => $stats,
            'logins'=> (int) ($stats['unique_credentials'] ?? 0),
        ];
    }

    return $result;
}

function unified_total_stats(): array
{
    $all = unified_provider_stats();
    $statsList = array_map(static fn ($row) => $row['stats'], $all);
    return unified_merge_stats($statsList);
}

function unified_console_entries(?string $onlySlug = null): array
{
    $entries = [];
    $labels = unified_provider_labels();
    $storages = unified_all_storages();

    foreach ($storages as $slug => $storage) {
        if ($onlySlug !== null && $slug !== $onlySlug) {
            continue;
        }

        $label = $labels[$slug] ?? $slug;
        foreach ($storage->getConsoleEntries() as $entry) {
            $entries[] = [
                'line'  => '[' . $label . '] ' . ($entry['line'] ?? ''),
                'color' => $entry['color'] ?? 'green',
                'slug'  => $slug,
            ];
        }
    }

    return $entries;
}

function unified_logins_txt(?string $onlySlug = null): string
{
    $lines = [];
    foreach (unified_console_entries($onlySlug) as $entry) {
        $line = (string) ($entry['line'] ?? '');
        if ($onlySlug !== null && str_starts_with($line, '[')) {
            $line = preg_replace('/^\[[^\]]+\]\s*/', '', $line) ?? $line;
        }
        if ($line !== '') {
            $lines[] = $line;
        }
    }

    return implode("\n", $lines) . ($lines ? "\n" : '');
}

function unified_ip_list(): array
{
    $ips = [];
    foreach (unified_all_storages() as $slug => $storage) {
        foreach ($storage->getIpAccessList() as $row) {
            $ip = (string) ($row['ip'] ?? '');
            if ($ip === '') {
                continue;
            }
            $row['providers'][] = $slug;
            if (!isset($ips[$ip]) || strcmp((string) $row['last_seen'], (string) $ips[$ip]['last_seen']) > 0) {
                $ips[$ip] = $row;
            } elseif (isset($ips[$ip])) {
                $ips[$ip]['providers'] = array_values(array_unique(array_merge(
                    $ips[$ip]['providers'] ?? [$slug],
                    [$slug]
                )));
            }
        }
    }

    $list = array_values($ips);
    usort($list, static fn ($a, $b) => strcmp((string) $b['last_seen'], (string) $a['last_seen']));
    return $list;
}

function unified_bot_list(): array
{
    $bots = [];
    foreach (unified_all_storages() as $slug => $storage) {
        foreach ($storage->getBotAccessList() as $row) {
            $ip = (string) ($row['ip'] ?? '');
            if ($ip === '') {
                continue;
            }
            $row['provider'] = $slug;
            if (!isset($bots[$ip]) || strcmp((string) $row['last_seen'], (string) $bots[$ip]['last_seen']) > 0) {
                $bots[$ip] = $row;
            }
        }
    }

    $list = array_values($bots);
    usort($list, static fn ($a, $b) => strcmp((string) $b['last_seen'], (string) $a['last_seen']));
    return $list;
}

function unified_set_ip_blocked(string $ip, bool $blocked): void
{
    foreach (unified_all_storages() as $storage) {
        $storage->setIpBlocked($ip, $blocked);
    }
}

function unified_clear_action(string $action): void
{
    foreach (unified_all_storages() as $storage) {
        switch ($action) {
            case 'clicks':
                $storage->clearClicks();
                break;
            case 'logs':
                $storage->clearLogs();
                break;
            case 'logins':
                $storage->clearLogins();
                break;
            case 'blocked_ips':
                $storage->clearAllBlockedIps();
                break;
        }
    }
}
