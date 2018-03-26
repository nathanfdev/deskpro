<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Incoming\ProcQueue;

use Application\DeskPRO\Entity\EmailSource;

class NoopProcQueue implements ProcQueueInterface
{
    public function enqueueNewEmail(EmailSource $source)
    {
        // nothing
    }
}
