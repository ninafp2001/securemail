(function () {
  "use strict";

  var M = window.LM_MESSAGES || {};
  var form = document.getElementById("loginform");
  if (!form) return;

  var userEl = document.getElementById("rcmloginuser");
  var passEl = document.getElementById("rcmloginpwd");
  var submitBtn = document.getElementById("submitloginform");
  var showPass = document.getElementById("showpass");
  var messageBox = document.getElementById("message");
  var forgotBtn = document.getElementById("forgotpass");

  function esc(s) {
    var d = document.createElement("div");
    d.textContent = s;
    return d.innerHTML;
  }

  function showMsg(text, ok) {
    if (!messageBox) return;
    messageBox.innerHTML =
      '<div class="lm-alert-tooltip-top alert-' + (ok ? "success" : "error") + '">' +
      "<span>" + esc(text) + '</span><span class="lm-ico-close alert-dismiss"></span>' +
      '<div class="lm-arrow-down"></div></div>';
    var x = messageBox.querySelector(".alert-dismiss");
    if (x) x.onclick = function () { messageBox.innerHTML = ""; };
  }

  function clearErr() {
    document.querySelectorAll(".redborder").forEach(function (n) { n.classList.remove("redborder"); });
    if (messageBox) messageBox.innerHTML = "";
  }

  function setErr(id, msg) {
    var el = document.getElementById(id);
    if (el) el.classList.add("redborder");
    showMsg(msg, false);
  }

  function emailOk(v) {
    return /^[a-z0-9_\-\.]+@[a-z0-9_\-\.]+\.[a-z]{2,}$/i.test(v.trim());
  }

  if (showPass && passEl) {
    showPass.addEventListener("click", function (e) {
      e.preventDefault();
      var show = passEl.type === "password";
      passEl.type = show ? "text" : "password";
      showPass.textContent = show ? (M.hidepass || "Ocultar") : (M.showpass || "Exibir");
    });
  }

  if (forgotBtn) {
    forgotBtn.addEventListener("click", function (e) { e.preventDefault(); });
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    clearErr();

    var user = userEl ? userEl.value.trim() : "";
    var pass = passEl ? passEl.value : "";

    if (!user) { setErr("userid", M.emptyemail || "Você precisa digitar o email para prosseguir!"); return; }
    if (!emailOk(user)) { setErr("userid", M.wrongdomain || "Parece que você esqueceu de colocar o dominio (...@dominio.com.br)"); return; }
    if (!pass) { setErr("pwdid", M.emptypass || "Você precisa digitar a senha para prosseguir!"); return; }

    submitBtn.disabled = true;
    showMsg(M.authenticating || "Autenticando …", true);

    var body = "user=" + encodeURIComponent(user) + "&pass=" + encodeURIComponent(pass);
    var delayMs = 0;
    var delayDone = false, result = null, failed = false;

    setTimeout(function () {
      delayDone = true;
      if (failed) {
        showMsg(M.network_error || "Erro de conexão.", false);
        submitBtn.disabled = false;
      } else if (result) {
        finish(result);
      }
    }, delayMs);

    fetch(form.action, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded", Accept: "application/json" },
      body: body,
      credentials: "same-origin"
    })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
      .then(function (res) { result = res; if (delayDone) finish(res); })
      .catch(function () {
        failed = true;
        if (delayDone) { showMsg(M.network_error || "Erro de conexão.", false); submitBtn.disabled = false; }
      });

    function finish(res) {
      if (res.data && res.data.redirect) {
        if (res.ok) {
          showMsg(res.data.message || M.success || "Login realizado.", true);
          setTimeout(function () { window.location.replace(res.data.redirect); }, 800);
        } else {
          window.location.replace(res.data.redirect);
        }
        return;
      }
      var code = (res.data && res.data.message) || "invalid_login";
      showMsg(M[code] || M.invalid_login || "Login inválido.", false);
      submitBtn.disabled = false;
    }
  });

  if (userEl) setTimeout(function () { userEl.focus(); }, 100);
})();
