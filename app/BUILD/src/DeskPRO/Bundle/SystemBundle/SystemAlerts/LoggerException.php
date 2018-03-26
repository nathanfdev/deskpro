<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts;

use Exception;

/**
 * Class LoggerException.
 */
class LoggerException extends Exception
{
    /**
     * @param Exception $e
     */
    public function __construct(Exception $e)
    {
        parent::__construct($e->getMessage(), $e->getCode(), $e);
    }
}
