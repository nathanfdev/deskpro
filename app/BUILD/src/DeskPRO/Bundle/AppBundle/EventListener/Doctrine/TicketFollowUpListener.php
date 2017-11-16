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

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketFollowUpUpdatedEvent;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TicketFollowUpListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ActionAlertsListener constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        /** @var $followUp TicketFollowUp */
        if (($followUp = $args->getEntity()) instanceof TicketFollowUp) {
            $this->onFollowUpUpdate($followUp, 'update');
        }

        return;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        /** @var $followUp TicketFollowUp */
        if (($followUp = $args->getEntity()) instanceof TicketFollowUp) {
            $this->onFollowUpUpdate($followUp, 'update');
        }

        return;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        /** @var $followUp TicketFollowUp */
        if (!($followUp = $args->getEntity()) instanceof TicketFollowUp) {
            return;
        }
        $this->onFollowUpUpdate($followUp, 'remove');
    }

    /**
     * @param TicketFollowUp $followUp
     * @param $action
     */
    private function onFollowUpUpdate(TicketFollowUp $followUp, $action)
    {
        $ticket = $followUp->getTicket();
        $this->container
            ->get('event_dispatcher')
            ->dispatch(
                TicketFollowUpUpdatedEvent::EVENT_NAME, new TicketFollowUpUpdatedEvent(
                $ticket,
                $action
            ));
    }
}
