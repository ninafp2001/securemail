<?php
declare(strict_types=1);

// Endpoint leve para health checks (Fly.io, Docker) — sem anti-bot nem redirect.
http_response_code(200);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
echo 'ok';
