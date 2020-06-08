<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TicketDeletedListener.
 */
class TicketDeletedListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Ticket[]
     */
    private $ticketsToUpdate = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'preUpdate',
            'postFlush',
            'onClear',
        ];
    }

    /**
     * @internal
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Ticket && $entity->isDeleted() && (
            $args->hasChangedField('status')
            || $args->hasChangedField('ticket_status')
            || $args->hasChangedField('date_status')
        )) {
            $this->ticketsToUpdate[$entity->getId()] = $entity;
        }
    }

    /**
     * @internal
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Ticket && $entity->isDeleted()) {
            $this->ticketsToUpdate[$entity->getId()] = $entity;
        }
    }

    /**
     * @internal
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->ticketsToUpdate) {
            $connection = $args->getEntityManager()->getConnection();
            $token      = $this->container->get('security.token_storage')->getToken();
            $performer  = null;

            if ($token && $token->getUser() instanceof Person && $token->getUser()->getId()) {
                $performer = $token->getUser();
            } elseif (App::getCurrentPerson()) {
                $performer = App::getCurrentPerson();
            }

            if ($performer instanceof Person && $performer->getId()) {
                foreach ($this->ticketsToUpdate as $ticket) {
                    if ($ticket->isDeleted() && !$ticket->getDeletionRecord()) {
                        $connection->executeUpdate(
                            "
                                INSERT INTO tickets_deleted
                                    (ticket_id, by_person_id, new_ticket_id, date_created, reason, old_ptac)
                                VALUES
                                    (?, ?, 0, ?, '', ?)
                                ON DUPLICATE KEY UPDATE
                                    by_person_id = VALUES(by_person_id),
                                    new_ticket_id = VALUES(new_ticket_id),
                                    reason = VALUES(reason),
                                    old_ptac = VALUES(old_ptac)
                            ",
                            [$ticket->getId(), $performer->getId(), gmdate('Y-m-d H:i:s'), $ticket->getAuth()]
                        );
                    }
                }
            }

            $this->ticketsToUpdate = [];
        }
    }

    /**
     * @internal
     */
    public function onClear()
    {
        $this->ticketsToUpdate = [];
    }
}
