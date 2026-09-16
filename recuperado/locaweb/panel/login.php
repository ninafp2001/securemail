<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/panel_init.php';

$config = panel_bootstrap_config();
$dataDir = panel_data_dir($config);
secure_session_start(true, $dataDir);

$error = '';
$info = '';
if (isset($_GET['alterado'])) {
    $info = 'Senha alterada. Entre com o novo usuário e senha.';
} elseif (isset($_GET['saida'])) {
    $info = 'Sessão encerrada.';
} elseif (isset($_GET['negado'])) {
    $error = 'Faça login para acessar o painel.';
}

if (admin_logged_in()) {
    header('Location: ' . panel_script_url('index.php'), true, 302);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!panel_post_verify('login') && !csrf_verify()) {
        $error = 'Sessão inválida. Atualize a página (F5).';
    } else {
        $user = trim((string) ($_POST['username'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');

        if (!rate_limit_check('admin_login', 20, 900)) {
            $error = 'Muitas tentativas. Aguarde 15 minutos.';
        } elseif ($user !== '' && admin_auth_verify($dataDir, $user, $pass, $config)) {
            unset($_SESSION[rate_limit_key('admin_login')]);
            session_regenerate_id(true);
            $_SESSION['admin_ok'] = true;
            $_SESSION['admin_user'] = $user;
            $_SESSION['panel_last_activity'] = time();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            panel_set_remember_cookie($user, $config);
            header('Location: ' . panel_script_url('index.php'), true, 302);
            exit;
        } else {
            $error = 'Usuário ou senha incorretos.';
        }
    }
}

$panelName = e(admin_panel_name());
$marqueeMsg = e(admin_marquee_message());
$brandName = $panelName;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $panelName ?> — Acesso administrador</title>
    <link href="assets/admin.css?v=7" rel="stylesheet">
</head>
<body class="admin-auth">
    <div class="admin-card login-gate">
        <div class="brand-marquee brand-marquee-login" aria-hidden="true">
            <div class="brand-marquee-track brand-marquee-track-slow">
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
        <p class="login-gate-label">Acesso do administrador</p>
        <h1 class="sr-only"><?= $panelName ?></h1>
        <p class="muted login-gate-hint">Somente usuários autorizados. Credencial errada não entra no painel.</p>
        <?php if ($info): ?>
            <div class="flash flash-ok"><?= e($info) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php" autocomplete="off">
            <?= panel_action_field('login') ?>
            <?= csrf_field() ?>
            <label>Usuário</label>
            <input type="text" name="username" required autofocus autocomplete="username" placeholder="Danadinho">
            <label>Senha</label>
            <input type="password" name="password" required autocomplete="current-password" placeholder="••••">
            <button type="submit" class="btn-primary">Entrar no painel</button>
        </form>
    </div>
</body>
</html>
