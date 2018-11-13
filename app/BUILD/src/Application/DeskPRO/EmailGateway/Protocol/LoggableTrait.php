<?php
/**
 * Copyright (c) DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Protocol;

use Orb\Log\Logger;

/**
 * Trait LoggableTrait
 * @package Application\DeskPRO\EmailGateway\Protocol
 */
trait LoggableTrait
{
    /**
     *  Logger
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

}
