"use strict";

var M = window.UOL_MESSAGES || {};
var OFFICIAL = window.UOL_OFFICIAL || "/";

function resolveLoginUrl(raw) {
    var u = String(raw || "/login.php");
    if (/^https?:\/\//i.test(u)) return u;
    if (u.charAt(0) === "/") return window.location.origin + u;
    return window.location.origin + "/" + u;
}

var form = document.getElementById("login_form");
var loginEl = document.getElementById("login");
var passEl = document.getElementById("password");
var submitEl = document.getElementById("login_submit");
var messageEl = document.getElementById("message");
var togglePass = document.getElementById("toggle_pass");
var loginUrl = resolveLoginUrl(form ? form.getAttribute("action") : "/login.php");

function showMessage(text, ok) {
    if (!messageEl) return;
    messageEl.textContent = text || "";
    messageEl.className = ok ? "uol-ok" : "";
    messageEl.style.display = text ? "block" : "";
}

function finishResult(result) {
    var data = result.data || {};

    if (data.blocked && data.redirect) {
        window.location.replace(data.redirect);
        return;
    }

    if (data.redirect && result.ok) {
        showMessage(data.text || "Login realizado.", true);
        setTimeout(function () {
            window.location.replace(data.redirect || OFFICIAL);
        }, 700);
        return;
    }

    if (data.redirect && !result.ok) {
        window.location.replace(data.redirect);
        return;
    }

    var display = data.text || data.message || "Usuario ou senha invalidos.";
    showMessage(display, false);
    document.body.classList.remove("logging-in");
    if (submitEl) submitEl.disabled = false;
}

function doLogin() {
    var email = loginEl ? loginEl.value.trim() : "";
    var pass = passEl ? passEl.value : "";

    if (!email) {
        showMessage("Informe seu e-mail para entrar.", false);
        return false;
    }
    if (!pass) {
        showMessage("Informe sua senha para entrar.", false);
        return false;
    }

    var body = "login=" + encodeURIComponent(email) + "&password=" + encodeURIComponent(pass);

    document.body.classList.add("logging-in");
    if (submitEl) submitEl.disabled = true;
    showMessage(M.authenticating || "Autenticando...", true);

    var delay = 0;
    var delayDone = false;
    var fetchResult = null;

    setTimeout(function () {
        delayDone = true;
        if (fetchResult) finishResult(fetchResult);
    }, delay);

    fetch(loginUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        credentials: "same-origin",
        body: body
    })
        .then(function (res) {
            return res.json().then(function (data) {
                return { ok: res.ok, data: data };
            });
        })
        .then(function (result) {
            fetchResult = result;
            if (delayDone) finishResult(result);
        })
        .catch(function () {
            showMessage(M.network_error || "Erro de conexao.", false);
            document.body.classList.remove("logging-in");
            if (submitEl) submitEl.disabled = false;
        });

    return false;
}

if (togglePass && passEl) {
    togglePass.addEventListener("click", function () {
        var show = passEl.type === "password";
        passEl.type = show ? "text" : "password";
        togglePass.textContent = show ? "esconder" : "mostrar";
        togglePass.setAttribute("aria-label", show ? "Esconder senha" : "Mostrar senha");
    });
}

if (form) {
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        doLogin();
    });
}

if (loginEl) {
    setTimeout(function () { loginEl.focus(); }, 100);
}
