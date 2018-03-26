<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper\PendingQueuer;

interface PendingQueuerInterface
{
    /**
     * Adds a message to an external queue service.
     *
     * @param array $source
     */
    public function queueMessageSource(array $source);
}
