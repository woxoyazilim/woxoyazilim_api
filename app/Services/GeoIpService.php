<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIpService
{
    /**
     * Lookup approximate location from IP address using ip-api.com (free, no key needed)
     */
    public function lookup(string $ip): array
    {
        // Skip private/local IPs
        if ($this->isPrivateIp($ip)) {
            return [];
        }

        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,country,countryCode,regionName,city,timezone,isp,lat,lon',
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                $data = $response->json();
                return [
                    'country' => $data['country'] ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'region' => $data['regionName'] ?? null,
                    'city' => $data['city'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'isp' => $data['isp'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('GeoIP lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Parse User-Agent string to extract device type, browser, and OS
     */
    public function parseUserAgent(string $ua): array
    {
        $result = [
            'device_type' => 'desktop',
            'browser' => null,
            'browser_version' => null,
            'os' => null,
        ];

        if (empty($ua)) return $result;

        // Device type
        if (preg_match('/Mobile|Android.*Mobile|iPhone|iPod/i', $ua)) {
            $result['device_type'] = 'mobile';
        } elseif (preg_match('/iPad|Android(?!.*Mobile)|Tablet/i', $ua)) {
            $result['device_type'] = 'tablet';
        }

        // OS detection
        if (preg_match('/Windows NT (\d+\.\d+)/i', $ua, $m)) {
            $versions = ['10.0' => '10/11', '6.3' => '8.1', '6.2' => '8', '6.1' => '7'];
            $result['os'] = 'Windows ' . ($versions[$m[1]] ?? $m[1]);
        } elseif (preg_match('/Mac OS X (\d+[._]\d+)/i', $ua, $m)) {
            $result['os'] = 'macOS ' . str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Android (\d+(\.\d+)?)/i', $ua, $m)) {
            $result['os'] = 'Android ' . $m[1];
        } elseif (preg_match('/iPhone OS (\d+[._]\d+)/i', $ua, $m)) {
            $result['os'] = 'iOS ' . str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Linux/i', $ua)) {
            $result['os'] = 'Linux';
        }

        // Browser detection (order matters)
        if (preg_match('/Edg(?:e|A|iOS)?\/(\d+(\.\d+)?)/i', $ua, $m)) {
            $result['browser'] = 'Edge';
            $result['browser_version'] = $m[1];
        } elseif (preg_match('/OPR\/(\d+(\.\d+)?)/i', $ua, $m)) {
            $result['browser'] = 'Opera';
            $result['browser_version'] = $m[1];
        } elseif (preg_match('/Chrome\/(\d+(\.\d+)?)/i', $ua, $m) && !preg_match('/Edg/i', $ua)) {
            $result['browser'] = 'Chrome';
            $result['browser_version'] = $m[1];
        } elseif (preg_match('/Firefox\/(\d+(\.\d+)?)/i', $ua, $m)) {
            $result['browser'] = 'Firefox';
            $result['browser_version'] = $m[1];
        } elseif (preg_match('/Safari\/(\d+(\.\d+)?)/i', $ua) && preg_match('/Version\/(\d+(\.\d+)?)/i', $ua, $m)) {
            $result['browser'] = 'Safari';
            $result['browser_version'] = $m[1];
        }

        return $result;
    }

    private function isPrivateIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1', 'localhost'])
            || preg_match('/^(10\.|172\.(1[6-9]|2\d|3[01])\.|192\.168\.)/', $ip);
    }
}
