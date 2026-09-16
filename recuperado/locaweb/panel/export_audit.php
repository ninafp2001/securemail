<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/panel_init.php';

$config = panel_bootstrap_config();
secure_session_start(true, panel_data_dir($config));
require_admin();

$csv = lab_audit_storage($config)->exportAuditCsv();
$filename = 'audit_' . date('Y-m-d_H-i') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($csv));
echo $csv;
exit;
