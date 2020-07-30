<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Util;

use Symfony\Component\HttpFoundation\IpUtils as BaseIpUtils;

class IpUtils extends BaseIpUtils
{
    /**
     * True if the host is local host.
     *
     * We know for sure if it's a loopback address or a reserved local network adress.
     *
     * This is used on cloud to filter out people trying (sometimes by mistake) using local mail servers.
     *
     * @param string $host
     *
     * @return true
     */
    public static function guessIsLocalNetworkHost($host)
    {
        $host = trim(strtolower($host));

        // well known local hosts
        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return true;
        }

        if (self::checkIp($host, [
            '127.0.0.0/8',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '::1/128',
            'fd00::/8',
        ])) {
            return true;
        }

        return false;
    }

    /**
     * Check a user-supplied host to see if it's allowed to be called upon
     *
     * @param string $host
     * @return bool
     */
    public static function isHostUserCallable($host)
    {
        $host = trim(strtolower($host));

        if (defined('DPC_IS_CLOUD') || !empty($GLOBALS['DPC_TESTING_IPUTILS_HOST_CALLABLE'])) {
            if (self::guessIsLocalNetworkHost($host)) {
                return false;
            }

            foreach ([
                 'internal.deskpro.com',
                 'rds.amazonaws.com',
                 'es.amazonaws.com',
                 'cache.amazonaws.com',
                 'deskpro-service.com'
            ] as $name) {
                if (strpos($host, $name) !== false) {
                    return false;
                }
            }

            if ($ip = filter_var($host, FILTER_VALIDATE_IP)) {
                // dont allow priv ips
                if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return false;
                }
            } else {
                $hostIp = self::resolveHostToIp($host);
                if (!$hostIp) {
                    return false;
                }

                $hostIp = trim(strtolower($hostIp));

                // prevents inf recursion
                if ($hostIp !== $host) {
                    return self::isHostUserCallable($hostIp);
                }
            }
        }

        return true;
    }

    /**
     * Extracts the host from a url and runs it against isHostUserCallable
     *
     * @param string $url
     * @param bool   $expectHttp True if you expect the protocol to be http/s
     * @return bool
     */
    public static function isUrlUserCallable($url, $expectHttp = true)
    {
        $info = @parse_url($url);

        if (empty($info['host'])) {
            return false;
        }

        if ($expectHttp) {
            $scheme = strtolower(!empty($info['scheme']) ? $info['scheme'] : '');
            if ($scheme !== 'http' && $scheme !== 'https') {
                return false;
            }
        }

        return self::isHostUserCallable($info['host']);
    }

    private static function resolveHostToIp($host)
    {
        static $cache = [];

        if (isset($GLOBALS['DPC_TESTING_IPUTILS_HOST_RESOLVE'][$host])) {
            return $GLOBALS['DPC_TESTING_IPUTILS_HOST_RESOLVE'][$host];
        }

        if (isset($cache[$host])) {
            return $cache[$host];
        }

        $hostIp = @gethostbyname($host);
        if (!$hostIp) {
            return false;
        }

        $cache[$host] = $hostIp;

        return $hostIp;
    }
}
