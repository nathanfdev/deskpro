<?php

/**
 * Orb.
 *
 * @category Logger
 */

namespace Orb\Logger\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class NullHandler extends AbstractHandler
{
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    public function handle(array $record)
    {
        return false === $this->bubble;
    }
}
