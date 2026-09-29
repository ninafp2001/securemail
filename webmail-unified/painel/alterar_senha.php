<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_once '/app/providers/bol/includes/admin_auth.php';

$config = painel_config();
$dataDir = painel_data_dir();
painel_start_session();
painel_require_admin();

$error = '';
$ok = '';
$deviceCount = admin_auth_device_count($dataDir, $config);
$totpOn = admin_auth_totp_enabled($dataDir, $config);
$credentialsLocked = $totpOn && $deviceCount >= TOTP_MAX_DEVICES;

if (!$credentialsLocked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!painel_post_verify('senha')) {
        $error = 'Token inválido.';
    } elseif ($totpOn && !painel_require_totp_post()) {
        $error = 'Código Google Authenticator inválido. Nada foi alterado.';
    } else {
        $user = trim((string) ($_POST['username'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        $pass2 = (string) ($_POST['password2'] ?? '');
        if ($pass === '' || $pass !== $pass2) {
            $error = 'Senhas não conferem.';
        } elseif (strlen($pass) < ADMIN_MIN_PASS_LEN) {
            $error = 'Senha deve ter no mínimo ' . ADMIN_MIN_PASS_LEN . ' caracteres.';
        } elseif (admin_auth_save($dataDir, $user, $pass, false, true)) {
            $_SESSION['admin_user'] = $user;
            header('Location: ' . painel_script_url('alterar_senha.php?ok=1'), true, 302);
            exit;
        } else {
            $error = 'Não foi possível salvar.';
        }
    }
}

if (isset($_GET['ok'])) {
    $ok = 'Usuário e senha atualizados. O 2FA cadastrado permanece o mesmo.';
}

require __DIR__ . '/includes/layout.php';
painel_header('Alterar senha', 'senha');
?>

<div class="panel">
    <h2>Alterar senha do painel</h2>
    <?php if ($ok): ?><div class="flash flash-ok"><?= e($ok) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>

    <?php if ($credentialsLocked): ?>
        <p class="panel-hint">
            Usuário, senha e os <?= TOTP_MAX_DEVICES ?> aparelhos 2FA já estão configurados.
            <strong>Nada será alterado aqui</strong> — use o login normal com Google Authenticator.
        </p>
        <p><a href="setup_2fa.php" class="nav-pill" style="display:inline-block">Ver status do 2FA</a></p>
    <?php else: ?>
        <p class="panel-hint">Ao salvar, o 2FA já cadastrado <strong>não é removido</strong>. Com 2 aparelhos ativos, esta tela fica só leitura.</p>
        <form method="post" class="panel-form panel-action-form" data-require-totp="<?= $totpOn ? '1' : '0' ?>">
            <?= painel_action_field('senha') ?>
            <label>Usuário</label>
            <input type="text" name="username" value="<?= e((string) ($_SESSION['admin_user'] ?? 'Danadinho')) ?>" required>
            <label>Nova senha (mín. <?= ADMIN_MIN_PASS_LEN ?> caracteres)</label>
            <input type="password" name="password" required>
            <label>Confirmar senha</label>
            <input type="password" name="password2" required>
            <button type="submit" class="btn-primary">Salvar senha</button>
        </form>
    <?php endif; ?>
</div>

<?php painel_footer(); ?>
