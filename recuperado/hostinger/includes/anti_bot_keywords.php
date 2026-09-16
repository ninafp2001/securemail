<?php
declare(strict_types=1);

/**
 * Palavras-chave em ISP/ORG/ASN para datacenter, cloud, VPS e VPN conhecidos (2024–2026).
 *
 * @return list<string>
 */
function anti_bot_datacenter_keywords(): array
{
    return [
        'amazon', 'aws', 'amazon web services', 'ec2', 'elastic compute',
        'google cloud', 'google llc', 'gcp', 'googleusercontent',
        'microsoft azure', 'azure', 'windows azure', 'msft',
        'digitalocean', 'digital ocean', 'linode', 'akamai', 'akamaitechnologies',
        'oracle cloud', 'oracle corporation', 'oci',
        'ibm cloud', 'softlayer', 'rackspace', 'vultr', 'choopa',
        'hetzner', 'ovh', 'ovhcloud', 'scaleway', 'contabo', 'kamatera',
        'upcloud', 'hostinger', 'ionos', '1&1', 'strato',
        'alibaba cloud', 'aliyun', 'tencent cloud', 'huawei cloud',
        'cloudflare', 'fastly', 'incapsula', 'imperva', 'stackpath',
        'leaseweb', 'quadranet', 'psychz', 'servermania', 'colocrossing',
        'datacamp', 'datacenter', 'data center', 'hosting', 'hosted',
        'vps', 'vpn', 'virtual private', 'proxy', 'tor exit',
        'nordvpn', 'expressvpn', 'surfshark', 'mullvad', 'protonvpn', 'proton ag',
        'cyberghost', 'private internet access', 'ipvanish', 'windscribe',
        'datapacket', 'm247', 'tzulo', 'packet', 'frantech', 'buyvm',
        'netcup', 'netlify', 'heroku', 'render.com', 'fly.io', 'railway',
        'semrush', 'ahrefs', 'moz.com', 'serpstat', 'screaming frog',
        'censys', 'shodan', 'binaryedge', 'zmap', 'masscan',
        'bot', 'crawler', 'spider', 'scraper', 'headless',
    ];
}

/**
 * Padrões de User-Agent típicos de bots, automação e scanners.
 *
 * @return list<string> regex (case-insensitive, sem delimitadores)
 */
function anti_bot_ua_patterns(): array
{
    return [
        'bot\b', 'crawl', 'spider', 'slurp', 'archiver', 'scanner',
        'curl\/', 'wget\/', 'python-requests', 'python\/', 'aiohttp',
        'java\/', 'go-http-client', 'okhttp', 'httpclient', 'libwww',
        'axios\/', 'postman', 'insomnia', 'httpie', 'scrapy',
        'headless', 'phantomjs', 'selenium', 'webdriver', 'puppeteer',
        'playwright', 'chromedriver', 'geckodriver', 'nightmare',
        'zgrab', 'nikto', 'nmap', 'masscan', 'sqlmap',
        'gptbot', 'oai-search', 'chatgpt-user', 'claudebot', 'anthropic',
        'perplexitybot', 'bytespider', 'petalbot', 'semrush', 'ahrefs',
        'dotbot', 'rogerbot', 'mj12bot', 'baiduspider', 'yandexbot',
        'facebookexternalhit', 'meta-externalagent', 'applebot',
        'bingpreview', 'duckduckbot', 'sogou', 'exabot',
        'undetected', 'automation', 'electron\/', 'node-fetch',
        'compatible;\s*$', '^java\/', '^ruby\/', '^php\/',
    ];
}
