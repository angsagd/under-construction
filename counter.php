<?php
declare(strict_types=1);

$allowedPages = [
    'index',
    'bugs',
    'code',
    'command',
    'countdown',
    'deploy',
    'errors',
    'tools',
    'website',
];

function finish(): void
{
    http_response_code(204);
    exit;
}

function hostName(string $host): string
{
    return strtolower(preg_replace('/:\d+$/', '', $host) ?? $host);
}

function sameHost(?string $url, string $host): bool
{
    if ($url === null || $url === '') {
        return true;
    }

    $urlHost = parse_url($url, PHP_URL_HOST);

    return is_string($urlHost) && hostName($urlHost) === hostName($host);
}

function isPrivateOrReservedIp(string $ip): bool
{
    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) === false;
}

function firstPublicIpFromHeader(string $value): ?string
{
    foreach (explode(',', $value) as $candidate) {
        $ip = trim($candidate);

        if (
            filter_var($ip, FILTER_VALIDATE_IP) !== false &&
            !isPrivateOrReservedIp($ip)
        ) {
            return $ip;
        }
    }

    return null;
}

function clientIp(): string
{
    $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';

    if ($remoteAddress !== '' && isPrivateOrReservedIp($remoteAddress)) {
        $proxyHeaders = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_TRUE_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED',
        ];

        foreach ($proxyHeaders as $header) {
            $value = $_SERVER[$header] ?? '';

            if ($value === '') {
                continue;
            }

            if ($header === 'HTTP_FORWARDED') {
                preg_match_all('/for="?([^;,"]+)/i', $value, $matches);
                $value = implode(',', $matches[1] ?? []);
            }

            $ip = firstPublicIpFromHeader($value);

            if ($ip !== null) {
                return $ip;
            }
        }
    }

    return filter_var($remoteAddress, FILTER_VALIDATE_IP) !== false
        ? $remoteAddress
        : '';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    finish();
}

$host = $_SERVER['HTTP_HOST'] ?? '';
$origin = $_SERVER['HTTP_ORIGIN'] ?? null;
$referer = $_SERVER['HTTP_REFERER'] ?? null;

if ($host === '' || !sameHost($origin, $host) || !sameHost($referer, $host)) {
    finish();
}

if ($origin !== null && $origin !== '') {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Vary: Origin');
}

$page = $_GET['page'] ?? '';

if (!is_string($page) || !in_array($page, $allowedPages, true)) {
    finish();
}

$logDirectory = __DIR__ . '/data';
$logFile = $logDirectory . '/counter.log';

if (!is_dir($logDirectory) && !mkdir($logDirectory, 0755, true) && !is_dir($logDirectory)) {
    finish();
}

$fileExists = is_file($logFile);
$handle = fopen($logFile, 'ab');

if ($handle === false) {
    finish();
}

if (flock($handle, LOCK_EX)) {
    if (!$fileExists || filesize($logFile) === 0) {
        fputcsv($handle, ['datetime', 'page', 'ip_address']);
    }

    $datetime = (new DateTimeImmutable())->format('Y-m-d H:i:s u');
    fputcsv($handle, [$datetime, $page, clientIp()]);
    fflush($handle);
    flock($handle, LOCK_UN);
}

fclose($handle);
finish();
