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
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->removePhoneCalls($args);

        return;
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->removePhoneCalls($args);
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $this->removePhoneCalls($args);
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function removePhoneCalls(LifecycleEventArgs $args)
    {
        $ticket = $args->getEntity();

        if ($ticket instanceof Ticket && ($ticket->getIsDeleted() || ($ticket->getIsHidden() && $ticket->getHiddenStatus() == 'spam'))) {
            Util::deleteTicketsCallRecords(
                $ticket,
                $this->container->get('doctrine.orm.default_entity_manager'),
                $this->container->get('blob.storage')
            );
        }

        return;
    }
}
