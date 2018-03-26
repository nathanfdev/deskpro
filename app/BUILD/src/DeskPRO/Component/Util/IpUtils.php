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
}
