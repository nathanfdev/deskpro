<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Net\Dns\Rdns;

class RdnsPhp implements RdnsInterface
{
    /**
     * @param string $ip
     *
     * @throws \RuntimeException
     *
     * @return string|null
     */
    public function lookup($ip)
    {
        return trim(gethostbyaddr($ip)) ?: null;
    }
}
