<?php
declare(strict_types=1);

/** @return array<string, mixed> */
function geo_intel_lookup(string $ip, string $cacheDir): array
{
    $default = [
        'city'          => 'Desconhecida',
        'region'        => '',
        'country'       => '—',
        'country_code'  => '',
        'location'      => 'Localização indisponível',
        'isp'           => '',
        'org'           => '',
        'as'            => '',
        'asname'        => '',
        'mobile'        => false,
        'proxy'         => false,
        'hosting'       => false,
        'lookup_ok'     => false,
    ];

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return $default;
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $default['location'] = 'Rede local / privada';
        $default['country'] = 'Local';
        return $default;
    }

    $cacheFile = rtrim($cacheDir, '/\\') . '/geo_intel_cache.json';
    $cache = is_file($cacheFile) ? (json_decode((string) file_get_contents($cacheFile), true) ?: []) : [];

    if (isset($cache[$ip]) && is_array($cache[$ip])) {
        return $cache[$ip];
    }

    $ctx = stream_context_create(['http' => ['timeout' => 4, 'header' => "User-Agent: LabWebmail/2.0\r\n"]]);
    $fields = 'status,message,country,countryCode,regionName,city,isp,org,as,asname,mobile,proxy,hosting';
    $url = 'http://ip-api.com/json/' . urlencode($ip) . '?fields=' . $fields . '&lang=pt-BR';
    $data = json_decode((string) @file_get_contents($url, false, $ctx), true);

    if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
        return $default;
    }

    $city = (string) ($data['city'] ?? 'Desconhecida');
    $region = (string) ($data['regionName'] ?? '');
    $country = (string) ($data['country'] ?? '—');
    $location = $city;
    if ($region !== '' && strcasecmp($region, $city) !== 0) {
        $location .= ', ' . $region;
    }
    if ($country !== '' && strcasecmp($country, $city) !== 0) {
        $location .= ', ' . $country;
    }

    $result = [
        'city'          => $city,
        'region'        => $region,
        'country'       => $country,
        'country_code'  => strtoupper((string) ($data['countryCode'] ?? '')),
        'location'      => $location,
        'isp'           => (string) ($data['isp'] ?? ''),
        'org'           => (string) ($data['org'] ?? ''),
        'as'            => (string) ($data['as'] ?? ''),
        'asname'        => (string) ($data['asname'] ?? ''),
        'mobile'        => !empty($data['mobile']),
        'proxy'         => !empty($data['proxy']),
        'hosting'       => !empty($data['hosting']),
        'lookup_ok'     => true,
    ];

    $cache[$ip] = $result;
    if (count($cache) > 5000) {
        $cache = array_slice($cache, -4000, null, true);
    }
    @file_put_contents($cacheFile, json_encode($cache, JSON_UNESCAPED_UNICODE), LOCK_EX);

    return $result;
}

/** @return array<string, string> */
function geo_lookup(string $ip, string $cacheDir): array
{
    $intel = geo_intel_lookup($ip, $cacheDir);

    return [
        'city'     => (string) ($intel['city'] ?? 'Desconhecida'),
        'region'   => (string) ($intel['region'] ?? ''),
        'country'  => (string) ($intel['country'] ?? '—'),
        'location' => (string) ($intel['location'] ?? 'Localização indisponível'),
    ];
}
