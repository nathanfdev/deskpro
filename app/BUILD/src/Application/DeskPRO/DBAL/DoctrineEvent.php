<?php

/**
 * DeskPRO.
 *
 * @category DBAL
 */

namespace Application\DeskPRO\DBAL;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * This connects some of the Doctrine events to the symfony event dispatcher.
 */
class DoctrineEvent extends \Symfony\Component\EventDispatcher\Event
{
    /**
     * @var mixed
     */
    protected $doctrine_event;

    /**
     * @var string
     */
    protected $event_type;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entity_manager;

    public function __construct($event_type, $doctrine_event)
    {
        $this->event_type     = $event_type;
        $this->doctrine_event = $doctrine_event;
        $this->entity_manager = $doctrine_event->getEntityManager();
        $this->entity         = null;

        if ($doctrine_event instanceof LifecycleEventArgs or $doctrine_event instanceof PreUpdateEventArgs) {
            $this->entity = $doctrine_event->getEntity();
        }
    }

    /**
     * The entity, or null if the event type doesnt have an entity.
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entity_manager;
    }

    /**
     * @return string
     */
    public function getEventType()
    {
        return $this->event_type;
    }

    /**
     * @return mixed
     */
    public function getDoctrineEvent()
    {
        return $this->doctrine_event;
    }
}
