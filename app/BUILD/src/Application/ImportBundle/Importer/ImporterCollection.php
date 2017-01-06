<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\ImportBundle\Importer;

use Application\ImportBundle\Model\PrimaryImportModelInterface;
use DeskPRO\Component\Util\AbstractCollection;

/**
 * Collection of entities collections
 * Uses as storage to export and write collections of entities in different orders.
 *
 * Class GenerateCollection
 */
class ImporterCollection extends AbstractCollection
{
    /**
     * @param PrimaryImportModelInterface $model
     *
     * @return $this
     */
    public function add(PrimaryImportModelInterface $model)
    {
        $this->collection[get_class($model)][$model->getOid()] = $model;

        return $this;
    }

    /**
     * Add an model collection.
     *
     * @param string $type
     * @param array  $models
     *
     * @return $this
     */
    public function addByType($type, array $models)
    {
        if (isset($this->collection[$type])) {
            $this->collection[$type] += $models;
        } else {
            $this->collection[$type] = $models;
        }

        return $this;
    }

    /**
     * Remove an model collection.
     *
     * @param PrimaryImportModelInterface $model
     *
     * @return $this
     */
    public function remove(PrimaryImportModelInterface $model)
    {
        if (isset($this->collection[get_class($model)][$model->getOid()])) {
            unset($this->collection[get_class($model)][$model->getOid()]);
        }

        return $this;
    }

    /**
     * Returns a collection of models.
     *
     * @param string $type
     *
     * @throws \Exception
     *
     * @return PrimaryImportModelInterface[]
     */
    public function getByType($type)
    {
        if (isset($this->collection[$type])) {
            return $this->collection[$type];
        }

        return [];
    }
}
