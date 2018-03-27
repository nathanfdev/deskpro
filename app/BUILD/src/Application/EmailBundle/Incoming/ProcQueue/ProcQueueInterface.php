<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Incoming\ProcQueue;

use Application\DeskPRO\Entity\EmailSource;

interface ProcQueueInterface
{
    /**
     * @param EmailSource $source
     */
    public function enqueueNewEmail(EmailSource $source);
}
