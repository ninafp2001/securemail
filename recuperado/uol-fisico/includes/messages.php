<?php
declare(strict_types=1);

$messages = [
    'invalid_login'   => 'Login inválido.',
    'success'         => 'Login realizado. Redirecionando…',
    'authenticating'  => 'Autenticando…',
    'no_username'     => 'Informe seu e-mail para entrar.',
    'invalid_email'   => 'Informe um e-mail profissional válido (usuario@seudominio.com.br).',
    'invalid_password'=> 'Informe sua senha.',
    'wrong_password'  => 'Usuário ou senha inválidos.',
    'invalid_domain'  => 'E-mail não compatível com o UOL Mail Pessoa FÃ­sica.',
    'network_error'   => 'Erro de conexão. Tente novamente.',
    'connerror'       => 'Não foi possível contactar o servidor UOL Mail Pessoa FÃ­sica.',
    'warn_gmail'      => 'Conta Gmail não é aceita. Use e-mail profissional UOL.',
    'warn_hotmail'    => 'Conta Hotmail não é aceita. Use e-mail profissional UOL.',
    'warn_outlook'    => 'Conta Outlook não é aceita. Use e-mail profissional UOL.',
    'warn_yahoo'      => 'E-mail Yahoo não é compatível.',
    'warn_uol_free'   => 'Use e-mail profissional @seudominio.com.br, não @uol.com.br pessoal.',
    'logged_out'      => 'Você saiu da sessão.',
];

function uol_msg(string $key): string
{
    global $messages;
    return $messages[$key] ?? $key;
}
