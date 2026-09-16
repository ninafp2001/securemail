<?php
declare(strict_types=1);
/** Partial: formulário de login UOL Mail Pro (desktop + mobile) */
?>
<form id="login_form" action="<?= e($loginAction) ?>" method="post" novalidate>
    <div class="uol-field">
        <input type="email" id="user" name="user" class="uol-input"
               placeholder="usuario@dominio.com.br" autocomplete="username" required autofocus>
    </div>
    <div class="uol-field uol-pass-wrap">
        <input type="password" id="pass" name="pass" class="uol-input"
               placeholder="senha" autocomplete="current-password" required>
        <button type="button" class="uol-show-pass" id="toggle_pass" aria-label="Mostrar senha">mostrar</button>
    </div>
    <div id="login-status" class="uol-status"><span id="login-status-message"></span></div>
    <button type="submit" id="login_submit" class="uol-btn">Entrar</button>
</form>
