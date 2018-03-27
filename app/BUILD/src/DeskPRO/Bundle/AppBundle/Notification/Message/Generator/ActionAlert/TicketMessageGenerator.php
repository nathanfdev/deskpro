<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\SystemEventGenerator;
use DeskPRO\Bundle\AppBundle\Notification\NotificationService;

/**
 * Class TicketMessageGenerator.
 */
class TicketMessageGenerator extends SystemEventGenerator
{
    /**
     * {@inheritdoc}
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        /* @var TicketUpdatedEvent $event */
        $messages = [];

        // process broadcast message probably idea to move it to standalone broadcast event is good

        if ($event->getEventType() === 'agent.filter-update' || $event->getEventType() === 'agent.ticket-updated') {
            $actionAlert = new ActionAlert(NotificationService::TARGET_BROADCAST, $event->getData(), $event->getName());
            $actionAlert->setBroadcast();
            $messages[] = $actionAlert;
        } else {
            foreach ($this->getTarget($event) as $target) {
                $actionAlert = new ActionAlert($target, $event->getData(), $event->getName());
                $messages[]  = $actionAlert;
            }
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        if ($event instanceof TicketUpdatedEvent) {
            return true;
        }

        return false;
    }

    /**
     * @param TicketUpdatedEvent $event
     *
     * @return Ticket
     */
    protected function getTicket(TicketUpdatedEvent $event)
    {
        $ticket_repo = $this->em->getRepository('DeskPRO:Ticket');
        /** @var Ticket $ticket */
        $ticket = $ticket_repo->findOneBy(['id' => $event->getTicketId()]);
        if (!$ticket) {
            throw new \InvalidArgumentException(sprintf('No ticket with id [ %s ] was found!', $event->getTicketId()));
        }

        return $ticket;
    }
}
