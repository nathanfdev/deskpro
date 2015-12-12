<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator;

use Application\ImportBundle\Entity;
use DeskPRO\Component\Util\AbstractCollection;

/**
 * Collection of entities collections
 * Uses as storage to export and write collections of entities in different orders.
 *
 * Class GenerateCollection
 */
final class GenerateCollection extends AbstractCollection
{
    /**
     * Add an entity collection.
     *
     * @param string            $type
     * @param Entity\Collection $entities
     *
     * @return $this
     */
    public function attach($type, Entity\Collection $entities)
    {
        if (isset($this->collection[$type])) {
            /** @var Entity\Collection $collection */
            $collection = $this->collection[$type];
            $collection->merge($entities);
        } else {
            $this->collection[$type] = $entities;
        }

        return $this;
    }

    /**
     * Remove an entity collection.
     *
     * @param Entity\EntityInterface $entity
     *
     * @return $this
     */
    public function detach(Entity\EntityInterface $entity)
    {
        foreach ($this->collection as $type => $type_collection) {
            /* @var Entity\Collection $type_collection */
            $type_collection->detach($entity);
        }

        return $this;
    }

    /**
     * Returns a collection of entities.
     *
     * @param string $type
     *
     * @throws \Exception
     *
     * @return Entity\Collection|Entity\EntityInterface[]
     */
    public function getByEntityType($type)
    {
        if (isset($this->collection[$type])) {
            return $this->collection[$type];
        }

        throw new \Exception(sprintf('Entities collection `%s` not found', $type));
    }

    /**
     * Returns containing entity types.
     *
     * @return string[]
     */
    public function getContainingEntityTypes()
    {
        $types = array();
        foreach ($this->collection as $type => $type_collection) {
            /** @var Entity\Collection $type_collection */
            if ($type_collection->count()) {
                $types[] = $type;
            }
        }

        return $types;
    }

    /**
     * Returns if collection has entities.
     *
     * @return bool
     */
    public function hasEntities()
    {
        foreach ($this->collection as $type_collection) {
            /** @var Entity\Collection $type_collection */
            if ($type_collection->count()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns if collection has entities of current type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasEntitiesByType($type)
    {
        if (isset($this->collection[$type])) {
            /** @var Entity\Collection $type_collection */
            $type_collection = $this->collection[$type];

            return $type_collection->count() > 0;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getSkippedCount()
    {
        $count = 0;
        foreach ($this->collection as $type_collection) {
            /* @var Entity\Collection $type_collection */
            $count += $type_collection->getSkippedCount();
        }

        return $count;
    }
}
