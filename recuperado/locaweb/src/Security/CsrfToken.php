<?php
declare(strict_types=1);

namespace Lab\Webmail\Security;

/**
 * CSRF assinado (stateless) — não depende de sessão PHP entre GET e POST.
 * Evita "Sessão expirada" quando cookies/sessão falham no Fly.io.
 */
final class CsrfToken
{
    public static function issue(int $ttlSeconds): string
    {
        $exp = time() + max(60, $ttlSeconds);
        $nonce = bin2hex(random_bytes(16));
        $payload = $exp . '.' . $nonce;
        $sig = hash_hmac('sha256', $payload, self::secret());

        return self::encode($payload . '.' . $sig);
    }

    public static function validate(?string $submitted): bool
    {
        if (!is_string($submitted) || $submitted === '') {
            return false;
        }

        $raw = self::decode($submitted);
        if ($raw === null) {
            return false;
        }

        $parts = explode('.', $raw, 3);
        if (count($parts) !== 3) {
            return false;
        }

        [$exp, $nonce, $sig] = $parts;
        if (!ctype_digit($exp) || (int) $exp < time()) {
            return false;
        }
        if (!preg_match('/^[a-f0-9]{32}$/', $nonce)) {
            return false;
        }

        $payload = $exp . '.' . $nonce;
        $expected = hash_hmac('sha256', $payload, self::secret());

        return hash_equals($expected, $sig);
    }

    private static function secret(): string
    {
        static $secret = null;
        if ($secret !== null) {
            return $secret;
        }

        $config = app_config();
        $fromEnv = (string) (getenv('CSRF_SECRET') ?: '');
        if ($fromEnv !== '') {
            return $secret = hash('sha256', $fromEnv);
        }

        $configured = (string) ($config['csrf_secret'] ?? '');
        if ($configured !== '') {
            return $secret = hash('sha256', $configured);
        }

        return $secret = hash('sha256', implode('|', [
            (string) (getenv('FLY_APP_NAME') ?: 'welcome-locaweb'),
            (string) ($config['panel_reset_key'] ?? 'csrf'),
            (string) ($config['data_dir'] ?? '/data'),
        ]));
    }

    private static function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function decode(string $value): ?string
    {
        $pad = (4 - strlen($value) % 4) % 4;
        $raw = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', $pad), true);

        return is_string($raw) && $raw !== '' ? $raw : null;
    }
}
