"use strict";

var DOM = { get: function (id) { return document.getElementById(id); } };

var MESSAGES = window.WEBMAIL_MESSAGES || window.MESSAGES || {};
var login_form = DOM.get("login_form");
var login_username_el = DOM.get("user");
var login_password_el = DOM.get("pass");
var login_submit_el = DOM.get("login_submit");

function show_status(message, level) {
    var container = DOM.get("login-status");
    var msgEl = DOM.get("login-status-message");
    if (!container || !msgEl) return;

    msgEl.textContent = message;
    container.className = "error-notice";
    if (level === "success") {
        container.className = "success-notice";
    } else if (level === "warn") {
        container.className = "warn-notice";
    } else if (level === "info") {
        container.className = "info-notice";
    }

    container.style.visibility = "visible";
    container.style.opacity = "1";
    container.style.display = "block";
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
    if (!login_username_el.value.trim()) {
        show_status(MESSAGES.no_username || "Informe o usuário.", "error");
        return false;
    }

    var userVal = login_username_el.value;
    var passVal = login_password_el.value;
    var body = "user=" + encodeURIComponent(userVal)
        + "&pass=" + encodeURIComponent(passVal);

    document.body.classList.add("logging-in");
    login_submit_el.disabled = true;
    show_status(MESSAGES.authenticating || "Autenticando …", "info");

    var delayMs = login_screen_delay_ms();
    var delayDone = false;
    var fetchResult = null;
    var fetchFailed = false;

    setTimeout(function () {
        delayDone = true;
        if (fetchResult !== null || fetchFailed) {
            if (fetchFailed) {
                show_status(MESSAGES.network_error || "Erro de rede.", "error");
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
        credentials: "same-origin",
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
                show_status(MESSAGES.network_error || "Erro de rede.", "error");
                document.body.classList.remove("logging-in");
                login_submit_el.disabled = false;
            }
        });

    return false;
}

function toggle_locales(show) {
    var localeBox = DOM.get("locale-container");
    var loginBox = DOM.get("login-container");
    if (!localeBox || !loginBox) return;

    if (show) {
        localeBox.style.visibility = "visible";
        localeBox.parentElement.style.display = "block";
        loginBox.style.display = "none";
    } else {
        localeBox.style.visibility = "hidden";
        localeBox.parentElement.style.display = "none";
        loginBox.style.display = "block";
    }
}

window.toggle_locales = toggle_locales;

var localeFooter = DOM.get("locale-footer");
if (localeFooter) {
    localeFooter.style.display = "block";
}

if (login_form) {
    login_form.onsubmit = do_login;
}

if (login_username_el) {
    setTimeout(function () { login_username_el.focus(); }, 150);
}

if (window.IS_LOGOUT) {
    var status = DOM.get("login-status");
    if (status) {
        status.style.visibility = "visible";
        status.style.display = "block";
    }
}
