(function () {
  const form = document.getElementById('login-form');
  const alert = document.getElementById('alert');
  const result = document.getElementById('result');
  const btnSubmit = document.getElementById('btn-submit');
  const btnTest = document.getElementById('btn-test');
  const testOnly = document.getElementById('test_only');

  if (!form) return;

  function hideMessages() {
    alert.classList.add('hidden');
    result.classList.add('hidden');
    alert.classList.remove('alert-ok');
    result.classList.remove('ok', 'fail');
  }

  function showFail(text) {
    alert.textContent = text;
    alert.classList.remove('hidden');
  }

  function showTestResult(entrou) {
    result.textContent = entrou ? 'ENTROU — e-mail e senha aceitos no lab.' : 'NÃO ENTROU — e-mail ou senha recusados.';
    result.classList.add(entrou ? 'ok' : 'fail');
    result.classList.remove('hidden');
  }

  async function postLogin(isTest) {
    hideMessages();
    testOnly.value = isTest ? '1' : '0';
    btnSubmit.disabled = true;
    if (btnTest) btnTest.disabled = true;

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      });

      const data = await res.json().catch(() => ({}));

      if (isTest) {
        showTestResult(res.ok && data.ok === true);
        return;
      }

      if (res.ok && data.ok && data.redirect) {
        window.location.href = data.redirect;
        return;
      }

      const msg = {
        invalid_credentials: 'NÃO ENTROU — e-mail ou senha incorretos.',
        invalid_session: 'Token expirado. Recarregue a página (F5).',
        too_many_attempts: 'Muitas tentativas. Aguarde alguns minutos.',
      }[data.error] || 'Não foi possível entrar.';

      showFail(msg);
    } catch {
      showFail('Erro de rede — o servidor PHP está rodando?');
    } finally {
      btnSubmit.disabled = false;
      if (btnTest) btnTest.disabled = false;
      testOnly.value = '0';
    }
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    postLogin(false);
  });

  if (btnTest) {
    btnTest.addEventListener('click', function () {
      postLogin(true);
    });
  }
})();
