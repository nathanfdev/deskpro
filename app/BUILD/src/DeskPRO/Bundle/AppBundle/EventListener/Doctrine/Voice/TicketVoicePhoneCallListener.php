<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Util;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TicketVoicePhoneCallListener.
 */
class TicketVoicePhoneCallListener
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
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $ticket = $args->getEntity();
        if ($ticket instanceof Ticket) {
            Util::deleteTicketsCallRecords(
                $ticket,
                $this->container->get('doctrine.orm.default_entity_manager'),
                $this->container->get('blob.storage')
            );
        }
    }
}
