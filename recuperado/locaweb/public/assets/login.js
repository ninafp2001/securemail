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
  var messageBox = document.getElementById("message");
  var forgotBtn = document.getElementById("forgotpass");

  function messageType(level) {
    if (level === "loading" || level === "info") return "loading";
    if (level === "danger" || level === "error") return "error";
    return "warning";
  }

  function showMessage(text, level) {
    if (!messageBox || !text) return;
    messageBox.className = "";
    messageBox.innerHTML =
      '<div class="' + messageType(level) + '">' + core.escapeHtml(text) + "</div>";
  }

  function clearMessage() {
    if (!messageBox) return;
    messageBox.innerHTML = "";
    messageBox.className = "";
  }

  function clearFieldErrors() {
    document.querySelectorAll(".lm-login-item.redborder").forEach(function (el) {
      el.classList.remove("redborder");
    });
    document.querySelectorAll(".errormessage").forEach(function (el) {
      el.remove();
    });
  }

  function setFieldError(itemId, message) {
    clearFieldErrors();
    clearMessage();
    var item = document.getElementById(itemId);
    if (!item) return;
    item.classList.add("redborder");
    var err = document.createElement("span");
    err.className = "errormessage";
    err.textContent = message;
    item.insertAdjacentElement("afterend", err);
  }

  function markLoginFieldsInvalid() {
    clearFieldErrors();
    var user = document.getElementById("userid");
    var pwd = document.getElementById("pwdid");
    if (user) user.classList.add("redborder");
    if (pwd) pwd.classList.add("redborder");
  }

  function updateFloatLabels() {
    [emailEl, passEl].forEach(function (input) {
      if (!input) return;
      var item = input.closest(".lm-login-item");
      if (item) item.classList.toggle("lm-filled", input.value.length > 0);
    });
  }

  function bindFloatLabels() {
    [emailEl, passEl].forEach(function (input) {
      if (!input) return;
      input.addEventListener("focus", function () {
        var item = input.closest(".lm-login-item");
        if (item) item.classList.add("lm-focused");
      });
      input.addEventListener("blur", function () {
        var item = input.closest(".lm-login-item");
        if (item) item.classList.remove("lm-focused");
        updateFloatLabels();
      });
      input.addEventListener("input", updateFloatLabels);
    });
    updateFloatLabels();
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
      setFieldError("userid", text);
      emailEl && emailEl.focus();
    },
    onBadEmail: function (text) {
      setFieldError("userid", text);
      emailEl && emailEl.focus();
    },
    onEmptyPassword: function (text) {
      setFieldError("pwdid", text);
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
        var user = document.getElementById("userid");
        if (user) user.classList.add("redborder");
      } else {
        markLoginFieldsInvalid();
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

  if (forgotBtn) {
    forgotBtn.addEventListener("click", function (e) {
      e.preventDefault();
    });
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    core.submitLogin(form, emailEl, passEl, submitBtn, ui);
  });

  bindFloatLabels();

  if (emailEl) {
    setTimeout(function () {
      emailEl.focus();
    }, 150);
  }
})();
