<?php
declare(strict_types=1);

const TOTP_MAX_DEVICES = 2;
const TOTP_PERIOD = 30;
const TOTP_DIGITS = 6;

function totp_base32_encode(string $bytes): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    for ($i = 0, $len = strlen($bytes); $i < $len; $i++) {
        $bits .= str_pad(decbin(ord($bytes[$i])), 8, '0', STR_PAD_LEFT);
    }
    $out = '';
    for ($i = 0, $bl = strlen($bits); $i + 5 <= $bl; $i += 5) {
        $out .= $alphabet[bindec(substr($bits, $i, 5))];
    }
    if (strlen($bits) % 5 !== 0) {
        $pad = str_pad(substr($bits, (int) (floor(strlen($bits) / 5) * 5)), 5, '0');
        $out .= $alphabet[bindec($pad)];
    }
    return $out;
}

function totp_base32_decode(string $b32): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $b32 = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32) ?? '');
    $bits = '';
    for ($i = 0, $len = strlen($b32); $i < $len; $i++) {
        $pos = strpos($alphabet, $b32[$i]);
        if ($pos === false) {
            continue;
        }
        $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
    }
    $out = '';
    for ($i = 0, $bl = strlen($bits); $i + 8 <= $bl; $i += 8) {
        $out .= chr(bindec(substr($bits, $i, 8)));
    }
    return $out;
}

function totp_generate_secret(): string
{
    return totp_base32_encode(random_bytes(20));
}

function totp_at(string $secretB32, ?int $time = null): string
{
    $time = $time ?? time();
    $counter = pack('N*', 0, (int) floor($time / TOTP_PERIOD));
    $hash = hash_hmac('sha1', $counter, totp_base32_decode($secretB32), true);
    $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
    $code = (
        ((ord($hash[$offset]) & 0x7f) << 24)
        | ((ord($hash[$offset + 1]) & 0xff) << 16)
        | ((ord($hash[$offset + 2]) & 0xff) << 8)
        | (ord($hash[$offset + 3]) & 0xff)
    ) % (10 ** TOTP_DIGITS);

    return str_pad((string) $code, TOTP_DIGITS, '0', STR_PAD_LEFT);
}

function totp_verify_secret(string $secretB32, string $code, int $window = 1): bool
{
    $code = preg_replace('/\D/', '', $code) ?? '';
    if (strlen($code) !== TOTP_DIGITS) {
        return false;
    }
    $now = time();
    for ($w = -$window; $w <= $window; $w++) {
        if (hash_equals(totp_at($secretB32, $now + ($w * TOTP_PERIOD)), $code)) {
            return true;
        }
    }
    return false;
}

function totp_provisioning_uri(string $secretB32, string $account, string $issuer): string
{
    $label = rawurlencode($issuer . ':' . $account);
    $issuerEnc = rawurlencode($issuer);
    return "otpauth://totp/{$label}?secret={$secretB32}&issuer={$issuerEnc}&digits=" . TOTP_DIGITS . '&period=' . TOTP_PERIOD;
}

function totp_qr_image_url(string $otpauthUri, int $size = 220): string
{
    return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
        . '&data=' . rawurlencode($otpauthUri);
}
