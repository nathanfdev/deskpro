<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
