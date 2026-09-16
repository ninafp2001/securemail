<?php
declare(strict_types=1);

namespace Lab\Webmail\Security;

final class SessionManager
{
    public static function start(array $config): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $name = (string) ($config['session_name'] ?? 'lab_webmail_sid');
        session_name($name);

        $savePath = (string) ($config['session_save_path'] ?? '');
        if ($savePath === '') {
            $dataDir = (string) ($config['data_dir'] ?? '');
            if ($dataDir !== '') {
                $savePath = rtrim($dataDir, '/\\') . '/sessions';
            }
        }
        if ($savePath !== '') {
            if (!is_dir($savePath)) {
                @mkdir($savePath, 0775, true);
            }
            if (is_dir($savePath) && is_writable($savePath)) {
                session_save_path($savePath);
            }
        }

        $cookiePath = '/';
        if (function_exists('url_prefix')) {
            $prefix = url_prefix($config);
            $cookiePath = $prefix === '' ? '/' : $prefix . '/';
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => $cookiePath,
            'domain'   => '',
            'secure'   => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();
    }

    private static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
