<?php
declare(strict_types=1);

/** @var array $config */
/** @var string $csrf */
/** @var string $loginAction */
/** @var string $panelUrl */
/** @var array<string, string> $lmMessages */

$logo = lm_alpha_skin('images/locaweb_logo.png', $config);
$logoFallback = lm_alpha_skin('images/locaweb_mobile.png', $config);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=yes">
    <meta name="description" content="Página de login do Webmail Locaweb. Acesse seu e-mail profissional com segurança.">
    <meta name="robots" content="noindex, nofollow">
    <title>Webmail :: Bem-vindo ao Webmail</title>
    <link rel="shortcut icon" href="<?= lm_alpha_skin('images/favicon.ico', $config) ?>">
    <link rel="stylesheet" type="text/css" href="<?= lm_alpha_skin('styles_general.css', $config) ?>">
    <link rel="stylesheet" type="text/css" href="<?= lm_asset('login-alpha-mobile.css', $config) ?>?v=2">
</head>
<body id="login">

<div id="message"></div>

<div id="loginBox">
    <form name="form" id="loginform" method="post" action="<?= lm_e($loginAction) ?>" novalidate>
        <input type="hidden" name="csrf" value="<?= lm_e($csrf) ?>">
        <div id="login_type" class="default">
            <table id="top">
                <tr>
                    <td id="logo">
                        <img src="<?= $logo ?>" alt="Locaweb" onerror="this.onerror=null;this.src='<?= $logoFallback ?>';">
                    </td>
                </tr>
            </table>
            <div id="loginTitle">Bem-vindo ao Webmail</div>

            <table id="content">
                <tr>
                    <td class="left">
                        <h2>Faça seu login</h2>
                        <div id="login_border">
                            <div id="login_inside">
                                <p class="text">
                                    <label for="rcmloginuser">E-mail:</label>
                                    <input name="email" id="rcmloginuser" type="email" autocomplete="username" required>
                                </p>
                                <p class="text">
                                    <label for="rcmloginpwd">Senha:</label>
                                    <input name="password" id="rcmloginpwd" type="password" autocomplete="current-password" required>
                                    <a href="#" id="showpass" class="lm-mobile-showpass">Exibir</a>
                                </p>
                                <div class="hr"></div>
                                <div id="loginButton">
                                    <input type="submit" id="submitloginform" class="button mainaction" value="Entrar">
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>

<script>
window.LM_MESSAGES = <?= json_encode($lmMessages, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
window.LM_PANEL_URL = <?= json_encode($panelUrl, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
</script>
<script src="<?= lm_asset('login-core.js', $config) ?>?v=2" defer></script>
<script src="<?= lm_asset('login-alpha-mobile.js', $config) ?>?v=2" defer></script>
</body>
</html>
