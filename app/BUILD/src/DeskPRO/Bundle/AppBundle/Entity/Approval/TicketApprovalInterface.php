<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use Application\DeskPRO\Entity\Ticket;

/**
 * Interface TicketApprovalInterface
 *
 * @package DeskPRO\Bundle\AppBundle\Entity\Approval
 */
interface TicketApprovalInterface
{
    /**
     * @return Ticket
     */
    public function getTicket();
}
