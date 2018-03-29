<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketFollowUpUpdatedEvent;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TicketFollowUpListener.
 */
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
