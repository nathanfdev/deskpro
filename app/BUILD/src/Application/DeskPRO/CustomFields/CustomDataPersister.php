<?php

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\EntityManager;

/**
 * this one is needed because there are too many legacy calls to flush in wrong places.
 *
 * Class CustomDataPersister
 */
class CustomDataPersister
{
    protected $toAdd;

    protected $toRemove;

    public function __construct()
    {
        $this->toAdd    = [];
        $this->toRemove = [];
    }

    /**
     * @param DomainObject $entity
     */
    public function add(DomainObject $entity)
    {
        $this->toAdd[] = $entity;
    }

    /**
     * @param array $add
     */
    public function addArray(array $add)
    {
        foreach ($add as $_add) {
            $this->add($_add);
        }
    }

    /**
     * @param DomainObject $entity
     */
    public function remove(DomainObject $entity)
    {
        $this->toRemove[] = $entity;
    }

    /**
     * @param array $remove
     */
    public function removeArray(array $remove)
    {
        foreach ($remove as $_remove) {
            $this->remove($_remove);
        }
    }

    /**
     * @param EntityManager $em
     */
    public function flush(EntityManager $em)
    {
        foreach ($this->toAdd as $add) {
            $em->persist($add);
        }
        $this->toAdd = [];

        foreach ($this->toRemove as $remove) {
            $em->remove($remove);
        }
        $this->toRemove = [];

        $em->flush();
    }
}
