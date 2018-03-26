<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Net\Dns\Rdns;

class RdnsNull implements RdnsInterface
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
        return;
    }
}
