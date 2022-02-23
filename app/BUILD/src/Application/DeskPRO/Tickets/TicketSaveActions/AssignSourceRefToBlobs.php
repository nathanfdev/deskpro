<?php

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Doctrine\ORM\EntityManager;

class AssignSourceRefToBlobs implements TicketSaveActionInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() != 'newticket') {
            return;
        }

        foreach ($ticket->getAttachments() as $attachment) {
            $blob = $attachment->getBlob();
            $blob->setIsTemp(false)->setSourceRef('ticket_attachment.'.$ticket->getId());
            $this->em->persist($blob);
        }
        $this->em->flush();
    }
}
