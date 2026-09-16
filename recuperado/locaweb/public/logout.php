<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use Lab\Webmail\Security\SessionManager;

$config = app_config();
SessionManager::start($config);

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: ' . url_path('/', $config));
exit;
