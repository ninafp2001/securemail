<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

painel_start_session();
painel_require_admin();

header('Content-Type: application/json; charset=utf-8');

$totals = unified_total_stats();
$providers = unified_provider_stats();
$providerOut = [];

foreach ($providers as $slug => $row) {
    $providerOut[$slug] = [
        'label' => $row['label'],
        'logins' => (int) ($row['stats']['unique_credentials'] ?? 0),
        'logins_today' => (int) ($row['stats']['logins_today'] ?? 0),
        'visits_today' => (int) ($row['stats']['visits_today'] ?? 0),
    ];
}

echo json_encode([
    'ok' => true,
    'time' => date('c'),
    'totals' => $totals,
    'providers' => $providerOut,
    'console_count' => count(unified_console_entries()),
], JSON_UNESCAPED_UNICODE);
