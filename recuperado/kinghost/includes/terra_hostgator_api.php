<?php
declare(strict_types=1);

require_once __DIR__ . '/http_client.php';
require_once __DIR__ . '/imap_verify.php';
require_once __DIR__ . '/uol_mailpro_http.php';

function terra_api_login_identities(string $email): array
{
    $email = strtolower(trim($email));
    $local = strstr($email, '@', true);
    $ids = [$email];
    if (is_string($local) && $local !== '') {
        $ids[] = $local;
    }

    return array_values(array_unique($ids));
}

function terra_api_verify_login(string $email, string $password, array $config): array
{
    $email = strtolower(trim($email));
    $variant = (string) ($config['verify_variant'] ?? 'fisico');
    $domain = terra_api_domain($email);

    if ($variant === 'fisico') {
        if ($domain !== 'terra.com.br' && !str_ends_with($domain, '.terra.com.br')) {
            return api_fail('invalid_email', 'Use seu e-mail @terra.com.br para acessar o Terra Mail pessoal.', 'terra_imap');
        }
    } else {
        if ($domain === 'terra.com.br' || str_ends_with($domain, '.terra.com.br')) {
            return api_fail('invalid_email', 'Clientes Terra Empresas: use usuario@seudominio.com.br', 'terra_imap');
        }
    }

    $httpCfg = array_merge($config, [
        'uol_mailpro_auth_url'  => (string) ($config['uol_mailpro_auth_url'] ?? 'https://mail.terra.com.br/auth'),
        'uol_mailpro_redir_url' => (string) ($config['uol_mailpro_redir_url'] ?? 'https://mail.terra.com.br/'),
        'uol_mailpro_timeout'   => (int) ($config['uol_mailpro_timeout'] ?? 12),
    ]);

    $hosts = ['imap.terra.com.br', 'mail.terra.com.br'];
    if ($variant === 'juridico' && $domain !== '') {
        array_unshift($hosts, 'imap.' . $domain, 'mail.' . $domain);
    }

    foreach (terra_api_login_identities($email) as $loginId) {
        if (imap_provider_verify($loginId, $password, $hosts)) {
            return api_ok('terra_imap');
        }
    }

    $lastFail = null;
    if ($variant === 'fisico' && !empty($config['terra_http_verify'])) {
        foreach (terra_api_login_identities($email) as $loginId) {
            $http = uol_mailpro_http_verify_login($loginId, $password, $httpCfg);
            if (!empty($http['ok'])) {
                $http['source'] = 'terra_http';
                return $http;
            }
            $lastFail = $http;
            if (($http['message_key'] ?? '') === 'wrong_password') {
                break;
            }
        }
    }

    if ($lastFail !== null && ($lastFail['message_key'] ?? '') === 'wrong_password') {
        return $lastFail;
    }

    return api_fail('wrong_password', 'Usuario ou senha invalidos.', 'terra_imap');
}

function terra_pop3_starttls_verify(string $email, string $password): bool
{
    $deadline = microtime(true) + 12;
    $fp = @stream_socket_client('tcp://pop.terra.com.br:110', $errno, $errstr, 4);
    if (!is_resource($fp)) {
        return false;
    }

    stream_set_timeout($fp, 4);
    if (!terra_pop3_expect_ok($fp, $deadline)) {
        fclose($fp);
        return false;
    }

    fwrite($fp, "STLS\r\n");
    if (!terra_pop3_expect_ok($fp, $deadline)) {
        fclose($fp);
        return false;
    }

    if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($fp);
        return false;
    }

    fwrite($fp, 'USER ' . $email . "\r\n");
    if (!terra_pop3_expect_ok($fp, $deadline)) {
        fclose($fp);
        return false;
    }

    fwrite($fp, 'PASS ' . $password . "\r\n");
    $ok = terra_pop3_expect_ok($fp, $deadline);
    @fwrite($fp, "QUIT\r\n");
    fclose($fp);

    return $ok;
}

function terra_pop3_expect_ok($fp, float $deadline): bool
{
    $line = imap_provider_read_line($fp, $deadline);
    return $line !== '' && str_starts_with($line, '+OK');
}

function hostgator_api_verify_login(string $email, string $password, array $config): array
{
    $email = strtolower(trim($email));
    $domain = terra_api_domain($email);

    if ($domain === '') {
        return api_fail('invalid_email', 'Informe um e-mail valido.', 'hostgator_imap');
    }

    $hosts = ['mail.' . $domain, 'imap.' . $domain, 'webmail.' . $domain];

    if (imap_provider_verify($email, $password, $hosts)) {
        return api_ok('hostgator_imap');
    }

    return api_fail('wrong_password', 'Login invalido. Verifique e-mail e senha.', 'hostgator_imap');
}

function terra_api_domain(string $email): string
{
    if (!str_contains($email, '@')) {
        return '';
    }
    return (string) substr($email, strrpos($email, '@') + 1);
}
