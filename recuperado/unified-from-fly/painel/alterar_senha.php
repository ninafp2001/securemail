<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_once '/app/providers/bol/includes/admin_auth.php';

$config = painel_config();
painel_start_session();
painel_require_admin();

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!painel_post_verify('senha')) {
        $error = 'Token inválido.';
    } else {
        $user = trim((string) ($_POST['username'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        $pass2 = (string) ($_POST['password2'] ?? '');
        if ($pass === '' || $pass !== $pass2) {
            $error = 'Senhas não conferem.';
        } elseif (strlen($pass) < 4) {
            $error = 'Senha muito curta.';
        } elseif (admin_auth_save(painel_data_dir(), $user, $pass)) {
            header('Location: ' . painel_script_url('login.php?alterado=1'), true, 302);
            exit;
        } else {
            $error = 'Não foi possível salvar.';
        }
    }
}

require __DIR__ . '/includes/layout.php';
painel_header('Alterar senha', 'senha');
?>

<div class="panel">
    <h2>Alterar senha do painel</h2>
    <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="panel-form">
        <?= painel_action_field('senha') ?>
        <label>Usuário</label>
        <input type="text" name="username" value="<?= e((string) ($_SESSION['admin_user'] ?? 'Danadinho')) ?>" required>
        <label>Nova senha</label>
        <input type="password" name="password" required>
        <label>Confirmar senha</label>
        <input type="password" name="password2" required>
        <button type="submit" class="btn-primary">Salvar</button>
    </form>
</div>

<?php painel_footer(); ?>
