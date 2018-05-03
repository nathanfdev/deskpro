<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery;

use Monolog\Logger;

class PusherLogger
{
    private $logger;

    /**
     * PusherLogger constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function log($msg)
    {
        $this->logger->log(Logger::INFO, $msg);
    }
}
