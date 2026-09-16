"use strict";

var UOL_ORIGIN = "https://conta.uol.com.br";

var REFERERS = {
    uol_webmail: UOL_ORIGIN + "/login?t=uol_webmail&env=visitante&dest=https://mail.uol.com.br/login/check_session",
    bol: UOL_ORIGIN + "/login?t=bol&env=visitante&dest=https://bmail.uol.com.br/login/check_session",
    default: UOL_ORIGIN + "/login?t=default"
};

function themeReferer(theme) {
    return REFERERS[theme] || REFERERS.default;
}

function loginQuery(theme, dest) {
    return (
        "/login?t=" +
        encodeURIComponent(theme || "default") +
        "&env=visitante&dest=" +
        encodeURIComponent(dest || "")
    );
}

function uolFetch(path, theme, init) {
    var target = UOL_ORIGIN + path;
    var headers = new Headers((init && init.headers) || {});
    headers.set("Origin", UOL_ORIGIN);
    headers.set("Referer", themeReferer(theme || "default"));
    if (!headers.has("Accept")) {
        headers.set("Accept", "application/json, text/plain, */*");
    }

    return fetch(target, {
        method: (init && init.method) || "GET",
        headers: headers,
        credentials: "include",
        mode: "cors",
        redirect: "follow",
        body: init && init.body ? init.body : undefined
    });
}

self.addEventListener("install", function () {
    self.skipWaiting();
});

self.addEventListener("activate", function (event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener("message", function (event) {
    var data = event.data || {};
    var port = event.ports && event.ports[0];
    if (!port) return;

    if (data.type === "uol-warmup") {
        var themeWarm = data.theme || "default";
        var dest = data.dest || "";
        uolFetch(loginQuery(themeWarm, dest), themeWarm, { method: "GET" })
            .then(function (resp) {
                return resp.text().then(function (html) {
                    port.postMessage({ ok: resp.ok, status: resp.status, html: html });
                });
            })
            .catch(function (err) {
                port.postMessage({ ok: false, error: String((err && err.message) || err) });
            });
        return;
    }

    if (data.type === "uol-auth-user") {
        var theme = data.theme || "default";
        var body = JSON.stringify(data.fields || {});
        uolFetch("/auth/user?t=" + encodeURIComponent(theme) + "&legacy=false", theme, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: body
        })
            .then(function (resp) {
                return resp.text().then(function (text) {
                    port.postMessage({
                        ok: true,
                        status: resp.status,
                        text: text
                    });
                });
            })
            .catch(function (err) {
                port.postMessage({ ok: false, error: String((err && err.message) || err) });
            });
    }
});
