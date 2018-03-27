<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Log;

interface LogCollectorInterface
{
    /**
     * Get the log for a message ref.
     *
     * @param $message_ref
     *
     * @return string
     */
    public function getLogForMessage($message_ref);
}
