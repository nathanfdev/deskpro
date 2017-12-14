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

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\DbDeliveryHandler;
use DeskPRO\Bundle\AppBundle\Notification\Event\Helpdesk\RefreshAgentInterfaceEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Snippet\SnippetsUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketFollowUpUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;

class HelpdeskAlertGenerator extends AbstractGenerator
{
    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface
     */
    public function createMessages(SystemEventInterface $event)
    {
        switch (get_class($event)) {
            case RefreshAgentInterfaceEvent::class:
                return $this->createRefreshAgentInterfaceAlerts($event);
            case SnippetsUpdatedEvent::class:
                return $this->createReloadSnippetsAlert($event);
            case TicketFollowUpUpdatedEvent::class:
                return $this->createReloadTicketFollowUpAlert($event);
            default:
                return [];
        }
    }

    /**
     * @param RefreshAgentInterfaceEvent $event
     *
     * @return ActionAlert[]
     */
    private function createRefreshAgentInterfaceAlerts(RefreshAgentInterfaceEvent $event)
    {
        // this event is dispatched to everyone, all the time.
        // we dont do just online because the helpdesk might've been
        // offline for enough time (e.g during upgrade) that agents
        // might technically be considered offline now.
        $agents = $this->em->getRepository(Person::class)->getAgents();

        // we always send through Db delivery because its possible
        // the client doesnt have an open connection to any other
        // service (e.g. imagine i just enabled pusher, i need this refresh
        // signal to go to already connected clients still using db)
        $meta = ['targettedHandlers' => [DbDeliveryHandler::TYPE]];

        $alerts = [];
        foreach ($agents as $agent) {
            $alerts[] = new ActionAlert($agent->getId(), [
                'who'               => $event->getWho() ?: 'System',
                'message'           => $event->getMessage(),
                'is_ignore_allowed' => $event->isIgnoreAllowed(),
                'reason_code'       => $event->getReasonCode(),
            ], $event->getName(), $meta);
        }

        return $alerts;
    }

    /**
     * @param SnippetsUpdatedEvent $event
     *
     * @return ActionAlert[]
     */
    private function createReloadSnippetsAlert(SnippetsUpdatedEvent $event)
    {
        $snippet = $event->getSnippet();
        $agents  = [];
        /* @var Person[] $agents */
        if ($snippet->isOwnershipGlobal()) {
            $agents = $this->em->getRepository(Person::class)->getAgents();
        } elseif ($snippet->getOwnershipTeams()) {
            $agents = $this->em->getRepository(Person::class)->getAgentsInTeams($snippet->getOwnershipTeams());
        }

        // we always send through Db delivery because its possible
        // the client doesnt have an open connection to any other
        // service (e.g. imagine i just enabled pusher, i need this refresh
        // signal to go to already connected clients still using db)
        $meta = ['targettedHandlers' => [DbDeliveryHandler::TYPE]];

        $alerts = [];
        foreach ($agents as $agent) {
            $alerts[] = new ActionAlert($agent->getId(), [
                'snippet_id' => $snippet->getId(),
                'action'     => $event->getAction(),
            ], $event->getName(), $meta);
        }

        return $alerts;
    }

    private function createReloadTicketFollowUpAlert(TicketFollowUpUpdatedEvent $event)
    {
        $ticket = $event->getTicket();
        if (!$ticket) {
            return [];
        }

        // we always send through Db delivery because its possible
        // the client doesnt have an open connection to any other
        // service (e.g. imagine i just enabled pusher, i need this refresh
        // signal to go to already connected clients still using db)
        $meta = ['targettedHandlers' => [DbDeliveryHandler::TYPE]];

        $alerts = [];
        $agents = $this->em->getRepository(Person::class)->getActiveAgents(true);
        foreach ($agents as $agentId) {
            $alerts[] = new ActionAlert($agentId, [
                'ticket_id' => $ticket->getId(),
                'action'    => $event->getAction(),
            ], $event->getName(), $meta);
        }

        return $alerts;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return
            $event instanceof RefreshAgentInterfaceEvent
            || $event instanceof SnippetsUpdatedEvent
            || $event instanceof TicketFollowUpUpdatedEvent;
    }
}
