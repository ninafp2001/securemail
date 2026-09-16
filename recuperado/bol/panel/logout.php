<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/panel_init.php';

$config = panel_bootstrap_config();
secure_session_start(true, panel_data_dir($config));
panel_logout();
header('Location: ' . panel_script_url('login.php') . '?saida=1', true, 302);
exit;
