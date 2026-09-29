<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$config = painel_config();
painel_start_session();

$error = '';
$info = '';
$needTotp = false;
if (isset($_GET['alterado'])) {
    $info = 'Senha alterada. Faça login com o usuário e senha novos.';
} elseif (isset($_GET['saida'])) {
    $info = 'Sessão encerrada.';
} elseif (isset($_GET['negado'])) {
    $error = 'Faça login para acessar o painel.';
} elseif (isset($_GET['2fa'])) {
    $info = 'Autenticador configurado com sucesso.';
}

if (painel_logged_in()) {
    header('Location: ' . painel_script_url('index.php'), true, 302);
    exit;
}

require_once '/app/providers/bol/includes/admin_auth.php';
$totpActive = admin_auth_totp_enabled(painel_data_dir(), $config);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!painel_post_verify('login')) {
        $error = 'Sessão inválida. Atualize a página.';
    } else {
        $user = trim((string) ($_POST['username'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        $totp = trim((string) ($_POST['totp_code'] ?? ''));

        if ($user === '' || !admin_auth_verify_password(painel_data_dir(), $user, $pass, $config)) {
            $error = 'Usuário ou senha incorretos.';
        } elseif ($totpActive) {
            if ($totp === '') {
                $needTotp = true;
                $error = 'Informe o código do Google Authenticator.';
            } elseif (!admin_auth_verify_totp_code(painel_data_dir(), $totp, $config)) {
                $error = 'Código 2FA inválido.';
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_password_ok'] = true;
                $_SESSION['admin_2fa_ok'] = true;
                $_SESSION['admin_user'] = $user;
                header('Location: ' . painel_script_url('index.php'), true, 302);
                exit;
            }
        } else {
            session_regenerate_id(true);
            $_SESSION['admin_password_ok'] = true;
            $_SESSION['admin_2fa_ok'] = false;
            $_SESSION['admin_user'] = $user;
            header('Location: ' . painel_script_url('index.php'), true, 302);
            exit;
        }
    }
}

$panelName = e(painel_name());
$marqueeMsg = e(painel_marquee());
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $panelName ?> — Acesso</title>
    <link href="/painel/assets/admin.css?v=10" rel="stylesheet">
</head>
<body class="admin-auth">
    <div class="admin-card login-gate">
        <div class="brand-marquee brand-marquee-login" aria-hidden="true">
            <div class="brand-marquee-track brand-marquee-track-slow">
                <span class="marquee-piece marquee-brand"><?= $panelName ?></span>
                <span class="marquee-dot">✦</span>
                <span class="marquee-piece marquee-msg"><?= $marqueeMsg ?></span>
            </div>
        </div>
        <p class="login-gate-label">Painel unificado</p>
        <?php if ($info): ?><div class="flash flash-ok"><?= e($info) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="login.php" autocomplete="off">
            <?= painel_action_field('login') ?>
            <label>Usuário</label>
            <input type="text" name="username" required autofocus placeholder="Usuário do painel" value="<?= e((string) ($_POST['username'] ?? '')) ?>">
            <label>Senha</label>
            <input type="password" name="password" required placeholder="••••">
            <?php if ($totpActive || $needTotp): ?>
            <label>Código Google Authenticator</label>
            <input type="text" name="totp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" <?= $needTotp ? 'required autofocus' : '' ?>>
            <?php endif; ?>
            <button type="submit" class="btn-primary">Entrar no painel</button>
        </form>
    </div>
</body>
</html>
