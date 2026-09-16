<?php
declare(strict_types=1);

require_once '/app/providers/bol/includes/device.php';

$panelRedirectPage = 'index.php';
?>

<div class="panel panel-acessos">
    <div class="panel-head">
        <h2>🌐 Acessos por IP</h2>
        <span class="count"><?= count($ipList) ?> IP(s) · bloqueio vale em todas as telas</span>
    </div>

    <div class="ip-access-list">
        <?php if ($ipList === []): ?>
            <div class="empty-state">Nenhum acesso por IP ainda.</div>
        <?php else: ?>
            <?php foreach ($ipList as $row):
                $status = ($row['status'] ?? 'liberado') === 'bloqueado' ? 'bloqueado' : 'liberado';
                $when = date('d/m/Y H:i:s', strtotime((string) $row['last_seen']));
                $ip = (string) ($row['ip'] ?? '—');
            ?>
            <article class="ip-row <?= $status === 'bloqueado' ? 'ip-row-blocked' : '' ?>">
                <div class="ip-row-top">
                    <div class="ip-row-info">
                        <?php if ($status === 'bloqueado'): ?>
                        <span class="status-badge status-bloqueado">Bloqueado</span>
                        <?php endif; ?>
                        <span class="ip-highlight"><?= e($ip) ?></span>
                        <span class="dot">·</span>
                        <span><?= e((string) ($row['os_icon'] ?? '🖥️')) ?> <?= e((string) ($row['os'] ?? '—')) ?></span>
                        <span class="dot">·</span>
                        <span>📍 <?= e((string) ($row['location'] ?? '—')) ?></span>
                        <span class="dot">·</span>
                        <span>Logins: <strong><?= (int) ($row['logins'] ?? 0) ?></strong></span>
                        <span class="dot">·</span>
                        <span class="ip-time"><?= e($when) ?></span>
                    </div>
                    <div class="ip-row-actions">
                        <?php if ($status === 'bloqueado'): ?>
                            <form method="post" action="ip_action.php" class="panel-action-form">
                                <?= painel_action_field('ip:unblock:' . $ip) ?>
                                <input type="hidden" name="ip" value="<?= e($ip) ?>">
                                <input type="hidden" name="action" value="unblock">
                                <button type="submit" class="btn-ip btn-ip-free">Liberar IP</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="ip_action.php" class="panel-action-form">
                                <?= painel_action_field('ip:block:' . $ip) ?>
                                <input type="hidden" name="ip" value="<?= e($ip) ?>">
                                <input type="hidden" name="action" value="block">
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

<?php if (!empty($botList)): ?>
<div class="panel panel-bots">
    <div class="panel-head">
        <h2>🤖 Bots bloqueados (anti-bot)</h2>
        <span class="count"><?= count($botList) ?> registro(s)</span>
    </div>
    <div class="ip-access-list">
        <?php foreach ($botList as $row): ?>
        <article class="ip-row">
            <div class="ip-row-info">
                <span class="ip-highlight"><?= e((string) ($row['ip'] ?? '—')) ?></span>
                <span class="dot">·</span>
                <span><?= e((string) ($row['reason'] ?? 'bot')) ?></span>
                <span class="dot">·</span>
                <span><?= e(date('d/m/Y H:i:s', strtotime((string) ($row['last_seen'] ?? 'now')))) ?></span>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
