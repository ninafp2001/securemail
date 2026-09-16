"use strict";

(function () {
    var overlay = document.getElementById("login-console");
    var body = document.getElementById("login-console-body");
    var openBtn = document.getElementById("btn-open-console");

    function renderConsole() {
        if (!body) return;
        var rows = window.LAB_AUDIT || [];
        if (!rows.length) {
            body.innerHTML = '<div class="console-empty">Aguardando logins…</div>';
            return;
        }
        body.innerHTML = rows.map(function (r) {
            var cls = r.color === "yellow" ? "line-yellow" : "line-green";
            if (r.color === "red") cls = "line-yellow";
            return '<div class="console-line ' + cls + '">' + escapeHtml(r.line) + "</div>";
        }).join("");
        body.scrollTop = body.scrollHeight;
    }

    function escapeHtml(s) {
        var d = document.createElement("div");
        d.textContent = s;
        return d.innerHTML;
    }

    function openConsole() {
        if (!overlay) return;
        renderConsole();
        overlay.hidden = false;
        overlay.setAttribute("aria-hidden", "false");
        document.body.classList.add("console-open");
    }

    function closeConsole() {
        if (!overlay) return;
        overlay.hidden = true;
        overlay.setAttribute("aria-hidden", "true");
        document.body.classList.remove("console-open");
    }

    if (openBtn) openBtn.addEventListener("click", openConsole);
    document.querySelectorAll("[data-close-console]").forEach(function (el) {
        el.addEventListener("click", closeConsole);
    });
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") closeConsole();
    });
    if (location.search.indexOf("console=1") !== -1) openConsole();

    document.querySelectorAll("form.panel-action-form, form[data-confirm]").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            var msg = form.getAttribute("data-confirm");
            if (msg && !window.confirm(msg)) e.preventDefault();
        });
    });

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
        setInterval(pollStats, 5000);
    }
})();
