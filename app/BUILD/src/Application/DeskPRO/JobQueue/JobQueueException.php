<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\JobQueue;

use Orb\Util\Strings;

/**
 * Anything that goes wrong that a worker (or router) detects.
 *
 * Default error code: 1545
 */
class JobQueueException extends \LogicException
{
    public function __construct($message, $code = 1545, \Exception $previous = null)
    {
        if (!Strings::startsWith('Job Queue', $message)) {
            $message = "Job Queue: $message";
        }
        parent::__construct($message, $code, $previous);
    }
}
