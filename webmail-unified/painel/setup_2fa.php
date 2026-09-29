<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_once '/app/providers/bol/includes/admin_auth.php';

$config = painel_config();
$dataDir = painel_data_dir();
painel_start_session();
painel_require_password_session();

$error = '';
$info = '';
$deviceCount = admin_auth_device_count($dataDir, $config);
$fullyRegistered = $deviceCount >= TOTP_MAX_DEVICES;

if ($fullyRegistered) {
    admin_auth_clear_pending_totp($dataDir);
    $_SESSION['admin_2fa_ok'] = true;
}

if (!$fullyRegistered && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!painel_post_verify('setup_2fa')) {
        $error = 'Sessão inválida. Atualize a página.';
    } else {
        $code = trim((string) ($_POST['totp_code'] ?? ''));
        $action = (string) ($_POST['setup_action'] ?? 'confirm');
        if ($action === 'skip_second' && $deviceCount >= 1) {
            admin_auth_clear_pending_totp($dataDir);
            $_SESSION['admin_2fa_ok'] = true;
            header('Location: ' . painel_script_url('index.php'), true, 302);
            exit;
        }
        if ($code === '') {
            $error = 'Informe o código de 6 dígitos do Google Authenticator.';
        } elseif (admin_auth_confirm_totp_enrollment($dataDir, $code, $config)) {
            $deviceCount = admin_auth_device_count($dataDir, $config);
            $_SESSION['admin_2fa_ok'] = true;
            if ($deviceCount >= TOTP_MAX_DEVICES) {
                header('Location: ' . painel_script_url('setup_2fa.php?ok=1'), true, 302);
                exit;
            }
            $info = 'Aparelho ' . $deviceCount . ' ativado. Você pode adicionar mais um ou ir ao painel.';
        } else {
            $error = 'Código inválido ou QR expirado. Gere um novo QR abaixo.';
            admin_auth_clear_pending_totp($dataDir);
        }
    }
}

$enroll = null;
if (!$fullyRegistered) {
    $enroll = admin_auth_pending_enrollment($dataDir, $config);
    if ($enroll === null) {
        $enroll = admin_auth_begin_totp_enrollment($dataDir, $config);
    }
    if ($enroll === null && $deviceCount >= TOTP_MAX_DEVICES) {
        $fullyRegistered = true;
        admin_auth_clear_pending_totp($dataDir);
    } elseif ($enroll === null) {
        $error = $error ?: 'Limite de ' . TOTP_MAX_DEVICES . ' aparelhos atingido. Não é possível gerar novo QR.';
    }
}

$panelName = e(painel_name());
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $panelName ?> — Google Authenticator</title>
    <link href="/painel/assets/admin.css?v=11" rel="stylesheet">
</head>
<body class="admin-auth">
    <div class="admin-card login-gate setup-2fa-card">
        <p class="login-gate-label">Segurança 2FA — Google Authenticator</p>

        <?php if ($fullyRegistered): ?>
            <div class="flash flash-ok">Seus <?= TOTP_MAX_DEVICES ?> aparelhos já foram cadastrados.</div>
            <p class="setup-2fa-hint">
                O 2FA está ativo e <strong>não será alterado</strong> daqui. Não é possível gerar novo QR
                nem cadastrar outro aparelho.
            </p>
            <p><a href="index.php" class="btn-primary" style="display:inline-block;text-align:center;text-decoration:none;width:100%">Voltar ao painel</a></p>
        <?php else: ?>
            <?php if (isset($_GET['senha'])): ?>
                <div class="flash flash-ok">Senha atualizada. Configure o autenticador abaixo (2FA anterior não é apagado se já existia).</div>
            <?php endif; ?>
            <?php if ($info): ?><div class="flash flash-ok"><?= e($info) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

            <?php if ($enroll !== null): ?>
                <p class="setup-2fa-hint">
                    Aparelho <strong><?= (int) $enroll['slot'] ?></strong> de <?= TOTP_MAX_DEVICES ?> —
                    escaneie o QR no Google Authenticator. Após <?= TOTP_MAX_DEVICES ?> aparelhos, novos QR são bloqueados.
                </p>
                <div class="setup-2fa-qr">
                    <img src="<?= e($enroll['qr']) ?>" width="220" height="220" alt="QR Code Google Authenticator">
                </div>
                <p class="setup-2fa-secret"><small>Chave manual: <code><?= e($enroll['secret']) ?></code></small></p>
                <form method="post" action="setup_2fa.php<?= isset($_GET['senha']) ? '?senha=1' : '' ?>" autocomplete="off">
                    <?= painel_action_field('setup_2fa') ?>
                    <input type="hidden" name="setup_action" value="confirm">
                    <label>Código de 6 dígitos</label>
                    <input type="text" name="totp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus placeholder="000000">
                    <button type="submit" class="btn-primary">Confirmar aparelho</button>
                </form>
            <?php endif; ?>

            <?php if ($deviceCount >= 1 && $deviceCount < TOTP_MAX_DEVICES): ?>
                <form method="post" action="setup_2fa.php" class="setup-2fa-skip">
                    <?= painel_action_field('setup_2fa') ?>
                    <input type="hidden" name="setup_action" value="skip_second">
                    <button type="submit" class="btn-sm">Ir ao painel (sem 2º aparelho)</button>
                </form>
            <?php elseif ($deviceCount >= 1): ?>
                <p><a href="index.php" class="btn-primary" style="display:inline-block;text-align:center;text-decoration:none">Entrar no painel</a></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
