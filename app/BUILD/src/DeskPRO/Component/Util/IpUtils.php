<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
