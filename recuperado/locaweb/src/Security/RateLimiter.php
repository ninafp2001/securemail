<?php
declare(strict_types=1);

namespace Lab\Webmail\Security;

final class RateLimiter
{
    public function __construct(private readonly string $dataDir) {}

    public function tooMany(string $bucket, int $maxAttempts, int $windowSeconds): bool
    {
        $dir = $this->dataDir . '/rate';
        if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
            return false;
        }

        $file = $dir . '/' . hash('sha256', $bucket) . '.json';
        $now = time();
        $data = ['count' => 0, 'reset' => $now + $windowSeconds];

        if (is_file($file)) {
            $raw = file_get_contents($file);
            $decoded = is_string($raw) ? json_decode($raw, true) : null;
            if (is_array($decoded) && isset($decoded['count'], $decoded['reset'])) {
                $data = $decoded;
            }
        }

        if ($now > (int) $data['reset']) {
            $data = ['count' => 0, 'reset' => $now + $windowSeconds];
        }

        $data['count'] = (int) $data['count'] + 1;
        file_put_contents($file, json_encode($data), LOCK_EX);

        return $data['count'] > $maxAttempts;
    }
}
