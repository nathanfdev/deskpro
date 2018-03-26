<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Persistance;

use DeskPRO\Bundle\AppBundle\Entity\Event;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use Doctrine\ORM\EntityManager;

class DbAdapter implements PersistenceAdapterInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function persist(SystemEventInterface $event)
    {
        $event_entity = new Event();
        $event_entity->setEvent($event);
        $this->em->persist($event_entity);
        $this->em->flush();
    }
}
