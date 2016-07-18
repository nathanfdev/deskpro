<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Model;

use DeskPRO\Component\Util\AbstractCollection;

/**
 * Exporting collection of entities.
 *
 * Class Collection
 *
 * @property PrimaryImportModelInterface[]|array $collection
 */
final class ImportModelCollection extends AbstractCollection
{
    /**
     * @var int
     */
    private $expected_count = 0;

    /**
     * @param int $expected_count
     *
     * @return $this
     */
    public function setExpectedCount($expected_count)
    {
        $this->expected_count = $expected_count;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpectedCount()
    {
        return $this->expected_count;
    }

    /**
     * @return int
     */
    public function getSkippedCount()
    {
        $diff = $this->expected_count - count($this->collection);

        return $diff > 0 ? $diff : 0;
    }

    /**
     * Add an entity.
     *
     * @param PrimaryImportModelInterface $entity
     *
     * @return $this
     */
    public function attach(PrimaryImportModelInterface $entity)
    {
        $this->collection[] = $entity;

        return $this;
    }

    /**
     * Remove an entity.
     *
     * @param PrimaryImportModelInterface $entity
     *
     * @return $this
     */
    public function detach(PrimaryImportModelInterface $entity)
    {
        $key = array_search($entity, $this->collection, true);

        if ($key !== false) {
            unset($this->collection[$key]);
        }

        return $this;
    }

    /**
     * Merge another entity collection.
     *
     * @param ImportModelCollection $collection
     *
     * @return $this
     */
    public function merge(ImportModelCollection $collection)
    {
        $this->expected_count += $collection->getExpectedCount();

        foreach ($collection as $entity) {
            /* @var PrimaryImportModelInterface $entity */
            $this->attach($entity);
        }

        return $this;
    }

    /**
     * Checks if all entities has import map key.
     *
     * @return bool
     */
    public function hasImportMapKey()
    {
        return count($this->collection) > 0;
    }

    /**
     * Returns containing entity oids.
     *
     * @return array
     */
    public function getOids()
    {
        return array_map(
            function (PrimaryImportModelInterface $entity) {
                return $entity->getOid();
            },
            $this->collection
        );
    }

    /**
     * Returns the max oid.
     *
     * @return mixed
     */
    public function getMaxOid()
    {
        return empty($this->collection) ? 0 : max(
            array_map(
                function (PrimaryImportModelInterface $entity) {
                    return $entity->getOid();
                },
                $this->collection
            )
        );
    }
}
