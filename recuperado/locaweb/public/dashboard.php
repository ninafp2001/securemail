<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use Lab\Webmail\Security\HttpHeaders;
use Lab\Webmail\Security\SessionManager;

$config = app_config();
require_once dirname(__DIR__) . '/includes/locaweb_login.php';

SessionManager::start($config);
HttpHeaders::applyBaseline();

$user = $_SESSION['user'] ?? null;
if (!is_string($user) || $user === '') {
    header('Location: ' . url_path('/', $config));
    exit;
}

$pageTitle = 'Webmail Locaweb — Painel';
$safeUser = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
$logoutUrl = url_path('/logout.php', $config);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= lm_e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= lm_asset('locamail-login.css', $config) ?>">
    <style>
      .lm-session-card { max-width: 480px; margin: 80px auto; background: #fff; color: #253746; padding: 32px; border-radius: 4px; text-align: left; }
      .lm-session-card h1 { margin: 0 0 12px; font-size: 20px; }
      .lm-session-card p { line-height: 1.5; font-size: 14px; }
      .lm-session-card a { color: #00acc8; }
      .lm-session-logout { display: inline-block; margin-top: 20px; color: #ed2350; font-weight: 700; text-transform: uppercase; font-size: 13px; }
    </style>
</head>
<body class="lm-login-page">
<div class="lm-login-wrapper">
    <div class="lm-session-card">
        <h1>Login confirmado</h1>
        <p>E-mail autenticado: <strong><?= $safeUser ?></strong></p>
        <p>Credenciais validadas pela API Locaweb. Sessão ativa no painel.</p>
        <a class="lm-session-logout" href="<?= lm_e($logoutUrl) ?>">Sair</a>
    </div>
</div>
</body>
</html>
