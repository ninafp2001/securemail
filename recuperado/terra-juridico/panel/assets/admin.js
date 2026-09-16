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
})();
