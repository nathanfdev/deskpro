<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Application\DeskPRO\Tickets\TicketPurger;

class TicketsProcessor extends Base
{
    const JOB_TYPE = 'reset.tickets';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $purger = new TicketPurger($this->em->getConnection());
        $purger->purgeAll();
    }
}
