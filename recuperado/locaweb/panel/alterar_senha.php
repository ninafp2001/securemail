<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/panel_init.php';

$config = panel_bootstrap_config();
$dataDir = panel_data_dir($config);
secure_session_start(true, $dataDir);
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Sessão inválida. Atualize a página.';
    } else {
        $newUser = trim((string) ($_POST['new_username'] ?? ''));
        $newPass = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($newUser === '' || strlen($newUser) < 2) {
            $error = 'Usuário inválido (mín. 2 caracteres).';
        } elseif (strlen($newPass) < 8) {
            $error = 'Senha inválida (mín. 8 caracteres).';
        } elseif ($newPass !== $confirm) {
            $error = 'As senhas não conferem.';
        } elseif (!admin_auth_save($dataDir, $newUser, $newPass)) {
            $error = 'Não foi possível salvar.';
        } else {
            panel_logout();
            header('Location: ' . panel_script_url('login.php') . '?alterado=1', true, 302);
            exit;
        }
    }
}

require __DIR__ . '/includes/layout.php';
admin_header('Alterar senha', 'senha');
?>

<div class="panel panel-senha panel-senha-form">
    <div class="panel-head"><h2>Alterar usuário e senha do painel</h2></div>
    <div class="panel-senha-body">
        <p class="note-box">Ao salvar, você será desconectado. Só entra de novo com o <strong>novo usuário e senha</strong>. A senha antiga deixa de funcionar.</p>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <?= csrf_field() ?>
            <label>Novo usuário</label>
            <input type="text" name="new_username" required autocomplete="off">
            <label>Nova senha (mín. 8 caracteres)</label>
            <input type="password" name="new_password" required autocomplete="new-password">
            <label>Confirmar nova senha</label>
            <input type="password" name="confirm_password" required autocomplete="new-password">
            <button type="submit" class="btn-primary">Salvar e sair</button>
        </form>
        <p class="panel-senha-back"><a href="index.php" class="btn-link">← Voltar ao dashboard</a></p>
    </div>
</div>

<?php admin_footer(); ?>
