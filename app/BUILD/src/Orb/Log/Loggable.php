<?php

/**
 * DeskPRO.
 */

namespace Orb\Log;

interface Loggable
{
    /**
     * Set the logger.
     *
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger);

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger();
}
