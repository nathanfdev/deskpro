<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\ActionApplicatorInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Slas\SlaClientMessageSender;
use Application\DeskPRO\Tickets\Slas\SlaProcessor;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RecalculateSlas implements TicketSaveActionInterface, ErrorCheckedInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var ActionApplicatorInterface
     */
    private $action_applicator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EntityManager             $em
     * @param ActionApplicatorInterface $action_applicator
     * @param EventDispatcherInterface  $eventDispatcher
     */
    public function __construct(
        EntityManager $em,
        ActionApplicatorInterface $action_applicator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em                = $em;
        $this->action_applicator = $action_applicator;
        $this->eventDispatcher   = $eventDispatcher;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() == 'noop') {
            return;
        }

        $cm_sender = new SlaClientMessageSender($this->em->getConnection(), $this->eventDispatcher);
        if ($context->getPersonContext() && $context->getPersonContext()->getId()) {
            $cm_sender->setPersonContext($context->getPersonContext());
        }

        $proc = new SlaProcessor($this->em, $this->action_applicator, $cm_sender);
        $proc->calculateSlas($ticket, $context);
    }
}
