<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Net\Dns\Rdns;

interface RdnsInterface
{
    /**
     * @param string $ip
     *
     * @throws \RuntimeException
     *
     * @return string|null
     */
    public function lookup($ip);
}
