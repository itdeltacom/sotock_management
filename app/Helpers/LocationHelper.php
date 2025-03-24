<?php

namespace App\Helpers;

class LocationHelper
{
    /**
     * Get location information from IP address
     * This is a simple implementation. In a production environment, 
     * you might want to use a more robust solution like MaxMind GeoIP or ipinfo.io
     * 
     * @param string $ipAddress
     * @return string|null
     */
    public static function getLocationFromIp($ipAddress)
    {
        // Skip for local or private IPs
        if (self::isPrivateIp($ipAddress)) {
            return 'Local Network';
        }
        
        try {
            // For demonstration purposes, we're using a free API
            // In production, you should consider a paid service with better reliability
            $response = file_get_contents("http://ip-api.com/json/{$ipAddress}");
            $data = json_decode($response, true);
            
            if ($data && $data['status'] === 'success') {
                return "{$data['city']}, {$data['regionName']}, {$data['country']}";
            }
            
            return null;
        } catch (\Exception $e) {
            // Log the error
            \Log::error("Failed to get location for IP: {$ipAddress}", [
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    /**
     * Check if an IP address is private or local
     * 
     * @param string $ipAddress
     * @return bool
     */
    public static function isPrivateIp($ipAddress)
    {
        // Local loopback
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return true;
        }
        
        // Private IP ranges
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $longIp = ip2long($ipAddress);
            
            // 10.0.0.0 - 10.255.255.255
            if ($longIp >= ip2long('10.0.0.0') && $longIp <= ip2long('10.255.255.255')) {
                return true;
            }
            
            // 172.16.0.0 - 172.31.255.255
            if ($longIp >= ip2long('172.16.0.0') && $longIp <= ip2long('172.31.255.255')) {
                return true;
            }
            
            // 192.168.0.0 - 192.168.255.255
            if ($longIp >= ip2long('192.168.0.0') && $longIp <= ip2long('192.168.255.255')) {
                return true;
            }
            
            // 169.254.0.0 - 169.254.255.255 (Link-local)
            if ($longIp >= ip2long('169.254.0.0') && $longIp <= ip2long('169.254.255.255')) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Parse user agent string to get browser and OS information
     * 
     * @param string $userAgent
     * @return array
     */
    public static function parseUserAgent($userAgent)
    {
        $browser = 'Unknown Browser';
        $os = 'Unknown OS';
        $device = 'Desktop';
        
        // Browser detection
        if (strpos($userAgent, 'Opera') || strpos($userAgent, 'OPR/')) {
            $browser = 'Opera';
        } elseif (strpos($userAgent, 'Edge')) {
            $browser = 'Microsoft Edge';
        } elseif (strpos($userAgent, 'Chrome')) {
            $browser = 'Google Chrome';
        } elseif (strpos($userAgent, 'Safari') && !strpos($userAgent, 'Chrome')) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Firefox')) {
            $browser = 'Mozilla Firefox';
        } elseif (strpos($userAgent, 'MSIE') || strpos($userAgent, 'Trident/')) {
            $browser = 'Internet Explorer';
        }
        
        // OS detection
        if (strpos($userAgent, 'Windows NT 10.0')) {
            $os = 'Windows 10';
        } elseif (strpos($userAgent, 'Windows NT 6.3')) {
            $os = 'Windows 8.1';
        } elseif (strpos($userAgent, 'Windows NT 6.2')) {
            $os = 'Windows 8';
        } elseif (strpos($userAgent, 'Windows NT 6.1')) {
            $os = 'Windows 7';
        } elseif (strpos($userAgent, 'Windows NT 6.0')) {
            $os = 'Windows Vista';
        } elseif (strpos($userAgent, 'Windows NT 5.1')) {
            $os = 'Windows XP';
        } elseif (strpos($userAgent, 'Windows NT 5.0')) {
            $os = 'Windows 2000';
        } elseif (strpos($userAgent, 'Mac')) {
            $os = 'macOS';
        } elseif (strpos($userAgent, 'X11') || strpos($userAgent, 'Linux')) {
            $os = 'Linux';
        }
        
        // Device detection (simple)
        if (strpos($userAgent, 'iPhone') || strpos($userAgent, 'iPad') || strpos($userAgent, 'Android') || strpos($userAgent, 'Mobile')) {
            $device = strpos($userAgent, 'Tablet') || strpos($userAgent, 'iPad') ? 'Tablet' : 'Mobile';
        }
        
        return [
            'browser' => $browser,
            'os' => $os,
            'device' => $device,
            'full' => $userAgent
        ];
    }
}