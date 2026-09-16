<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/panel_init.php';
header('Location: ' . panel_script_url('index.php') . '?console=1', true, 302);
exit;
