<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok'      => true,
    'service' => 'webmail-unified',
    'app'     => getenv('FLY_APP_NAME') ?: 'security-webmail',
    'time'    => gmdate('c'),
], JSON_UNESCAPED_UNICODE);
