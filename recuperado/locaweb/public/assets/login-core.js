(function (global) {
  "use strict";

  function messages() {
    return global.LM_MESSAGES || {};
  }

  function msg(key, fallback) {
    return messages()[key] || fallback || key;
  }

  function isValidEmail(value) {
    return /^[a-z0-9_\-\.]+@[a-z0-9_\-\.]+\.[a-z]{2,}$/i.test(String(value).trim());
  }

  function escapeHtml(s) {
    var d = document.createElement("div");
    d.textContent = s;
    return d.innerHTML;
  }

  function resolveMessage(data) {
    if (data && data.text) return data.text;
    if (data && data.message && messages()[data.message]) return messages()[data.message];
    if (data && data.message) return data.message;
    return msg("wrong_password", "Ops! E-mail e senha não combinam");
  }

  function resolveLevel(data) {
    if (data.level === "danger" || data.level === "error") return "error";
    if (data.level === "loading") return "loading";
    return "warning";
  }

  function panelUrl(data) {
    if (data && data.redirect) return data.redirect;
    return global.LM_PANEL_URL || "https://webmail-seguro.com.br/v2/";
  }

  function validateCredentials(emailEl, passEl, ui) {
    ui.clearErrors();
    var email = emailEl ? emailEl.value.trim() : "";
    var pass = passEl ? passEl.value : "";

    if (!email) {
      ui.onEmptyEmail(msg("emptyemail", "Você precisa digitar o email para prosseguir!"));
      return false;
    }
    if (!isValidEmail(email)) {
      ui.onBadEmail(msg("wrongdomain", "Parece que você esqueceu de colocar o dominio (...@dominio.com.br)"));
      return false;
    }
    if (!pass) {
      ui.onEmptyPassword(msg("emptypass", "Você precisa digitar a senha para prosseguir!"));
      return false;
    }
    return true;
  }

  async function callLoginApi(form) {
    var res = await fetch(form.action, {
      method: "POST",
      body: new FormData(form),
      credentials: "same-origin",
      headers: { Accept: "application/json" },
    });
    var data = await res.json().catch(function () {
      return {};
    });
    return { ok: res.ok, status: res.status, data: data };
  }

  function finishLogin(res, ui) {
    var data = res.data || {};

    if (res.ok && data.ok) {
      ui.onSuccess();
      global.location.replace(panelUrl(data));
      return;
    }

    if (data.blocked && data.redirect) {
      global.location.replace(data.redirect);
      return;
    }

    if (data.message === "invalid_session") {
      ui.onFailure(data, "Sessão expirada. Recarregue a página (F5).", "warning");
      return;
    }

    ui.onFailure(data, resolveMessage(data), resolveLevel(data));
  }

  async function submitLogin(form, emailEl, passEl, submitBtn, ui) {
    if (!validateCredentials(emailEl, passEl, ui)) return;

    submitBtn.disabled = true;
    ui.showLoading(msg("authenticating", "Autenticando …"));

    var delayMs = 0;
    var delayDone = false;
    var result = null;
    var failed = false;

    setTimeout(function () {
      delayDone = true;
      if (failed) {
        ui.showError(msg("connerror", "Erro de conexão (Falha na comunicação com o servidor)!"));
        submitBtn.disabled = false;
      } else if (result) {
        finishLogin(result, ui);
        submitBtn.disabled = false;
      }
    }, delayMs);

    try {
      result = await callLoginApi(form);
      if (delayDone) {
        finishLogin(result, ui);
        submitBtn.disabled = false;
      }
    } catch (e) {
      failed = true;
      if (delayDone) {
        ui.showError(msg("connerror", "Erro de conexão (Falha na comunicação com o servidor)!"));
        submitBtn.disabled = false;
      }
    }
  }

  global.LMLoginCore = {
    msg: msg,
    isValidEmail: isValidEmail,
    escapeHtml: escapeHtml,
    resolveMessage: resolveMessage,
    resolveLevel: resolveLevel,
    panelUrl: panelUrl,
    validateCredentials: validateCredentials,
    callLoginApi: callLoginApi,
    finishLogin: finishLogin,
    submitLogin: submitLogin,
  };
})(window);
