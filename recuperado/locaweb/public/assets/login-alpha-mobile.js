(function () {
  "use strict";

  var core = window.LMLoginCore;
  if (!core) return;

  var form = document.getElementById("loginform");
  if (!form) return;

  var emailEl = document.getElementById("rcmloginuser");
  var passEl = document.getElementById("rcmloginpwd");
  var submitBtn = document.getElementById("submitloginform");
  var showPassBtn = document.getElementById("showpass");
  var globalMsg = document.getElementById("message");

  function messageClass(level) {
    if (level === "loading" || level === "info") return "loading";
    if (level === "danger" || level === "error") return "error";
    return "warning";
  }

  function showMessage(text, level) {
    if (!globalMsg || !text) return;
    globalMsg.className = "global-msg visible";
    globalMsg.innerHTML =
      '<div class="' + messageClass(level) + '">' + core.escapeHtml(text) + "</div>";
  }

  function clearMessage() {
    if (!globalMsg) return;
    globalMsg.className = "global-msg";
    globalMsg.innerHTML = "";
  }

  function clearFieldErrors() {
    [emailEl, passEl].forEach(function (el) {
      if (el) el.classList.remove("field-error");
    });
  }

  function markFieldsInvalid() {
    clearFieldErrors();
    if (emailEl) emailEl.classList.add("field-error");
    if (passEl) passEl.classList.add("field-error");
  }

  function togglePassword() {
    if (!passEl || !showPassBtn) return;
    var show = passEl.type === "password";
    passEl.type = show ? "text" : "password";
    showPassBtn.textContent = show
      ? core.msg("hidepass", "Ocultar")
      : core.msg("showpass", "Exibir");
  }

  var ui = {
    clearErrors: function () {
      clearFieldErrors();
      clearMessage();
    },
    onEmptyEmail: function (text) {
      clearFieldErrors();
      showMessage(text, "warning");
      emailEl && emailEl.focus();
    },
    onBadEmail: function (text) {
      clearFieldErrors();
      showMessage(text, "warning");
      emailEl && emailEl.focus();
    },
    onEmptyPassword: function (text) {
      clearFieldErrors();
      showMessage(text, "warning");
      passEl && passEl.focus();
    },
    showLoading: function (text) {
      clearFieldErrors();
      showMessage(text, "loading");
    },
    showError: function (text) {
      showMessage(text, "error");
    },
    onSuccess: function () {
      clearMessage();
    },
    onFailure: function (data, text, level) {
      if (data.message === "invalid_domain" || data.error === "invalid_domain") {
        if (emailEl) emailEl.classList.add("field-error");
      } else {
        markFieldsInvalid();
      }
      showMessage(text, level);
    },
  };

  if (showPassBtn) {
    showPassBtn.addEventListener("click", function (e) {
      e.preventDefault();
      togglePassword();
    });
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    core.submitLogin(form, emailEl, passEl, submitBtn, ui);
  });

  if (emailEl) {
    setTimeout(function () {
      emailEl.focus();
    }, 150);
  }
})();
