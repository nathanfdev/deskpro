<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Logging;

use Monolog\Logger;

/**
 * This is a version of Monolog Logger that we use in DeskPRO that simply won't throw an exception
 * during a log. If an exception occurs in the normal Monolog Logger, we log it the deskpro way and move on.
 */
class NoExceptionsLogger extends Logger
{
    public function addRecord($level, $message, array $context = [])
    {
        try {
            return parent::addRecord($level, $message, $context);
        } catch (\Exception $e) {
            // this is disabled for now because it clogs the normal error.log file with an exception trace
            // dozens of times per request. ignore log exceptions silently.
        }
    }
}
