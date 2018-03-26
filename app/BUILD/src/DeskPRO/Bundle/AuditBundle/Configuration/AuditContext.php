<?php

namespace DeskPRO\Bundle\AuditBundle\Configuration;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AuditBundle\Log\Performer;

/**
 * Class AuditContext.
 */
class AuditContext
{
    /**
     * @var EntityInterface|DomainObject
     */
    private $entity;

    /**
     * @var string
     */
    private $action;

    /**
     * @var array
     */
    private $changeSet;

    /**
     * @var Performer
     */
    private $performer;

    /**
     * AuditContext constructor.
     *
     * @param string                       $action
     * @param Performer                    $performer
     * @param EntityInterface|DomainObject $entity
     * @param array                        $changeSet
     */
    public function __construct($action, Performer $performer, $entity = null, array $changeSet = [])
    {
        // entity is set and it's not and object OR it's neither EntityInterface nor DomainObject instance
        if (
            $entity &&
            !(is_object($entity) && ($entity instanceof EntityInterface || $entity instanceof DomainObject))
        ) {
            throw new \InvalidArgumentException(
                'An entity passed to AuditContext should be an EntityInterface or DomainObject instance'
            );
        }

        $this->action    = $action;
        $this->entity    = $entity;
        $this->performer = $performer;
        $this->changeSet = $changeSet;
    }

    /**
     * @return DomainObject|EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * @return Performer
     */
    public function getPerformer()
    {
        return $this->performer;
    }
}
