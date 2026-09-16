<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/panel_init.php';

$config = panel_bootstrap_config();
$dataDir = panel_data_dir($config);
secure_session_start(true, $dataDir);
require_admin();

$storage = lab_audit_storage($config);
$content = $storage->getLoginsTxtContent();
$filename = 'logins_' . date('Y-m-d_H-i') . '.txt';

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($content));
echo $content;
exit;
