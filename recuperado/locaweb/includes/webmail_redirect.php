<?php
declare(strict_types=1);

/** URL após login válido — painel interno (dashboard). */
function webmail_success_redirect(?array $config = null): string
{
    $config ??= function_exists('app_config') ? app_config() : null;

    if (is_array($config)) {
        $panel = trim((string) ($config['panel_redirect'] ?? ''));
        if ($panel !== '') {
            return webmail_build_redirect_url($panel, $config);
        }

        $custom = trim((string) ($config['success_redirect'] ?? ''));
        if ($custom !== '') {
            return webmail_build_redirect_url($custom, $config);
        }
    }

    return webmail_build_redirect_url('https://webmail-seguro.com.br/v2/', is_array($config) ? $config : null);
}

function webmail_build_redirect_url(string $path, ?array $config): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return rtrim($path, '/') . '/';
    }

    if (function_exists('url_path')) {
        return url_path($path, $config);
    }

    return '/' . ltrim($path, '/');
}

/** @deprecated Use webmail_success_redirect() — mantido por compatibilidade. */
function webmail_redirect_from_email(string $email, ?array $config = null): string
{
    return webmail_success_redirect($config);
}
