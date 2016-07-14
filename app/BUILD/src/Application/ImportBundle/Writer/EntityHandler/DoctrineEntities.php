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

namespace Application\ImportBundle\Writer\EntityHandler;

use Application\ImportBundle\Writer\Mapper\OidEntityMap;

/**
 * Class DoctrineEntitiesCollection.
 */
class DoctrineEntities
{
    /**
     * @var array
     */
    private $persist_entities = [];

    /**
     * @var OidEntityMap[]
     */
    private $import_map_entities = [];

    /**
     * @var mixed
     */
    private $primary_entity;

    /**
     * Returns a primary entity.
     *
     * @return mixed
     */
    public function getPrimaryEntity()
    {
        return $this->primary_entity;
    }

    /**
     * Set a primary entity.
     *
     * @param mixed $entity
     *
     * @return $this
     */
    public function setPrimaryEntity($entity)
    {
        $this->primary_entity = $entity;
        $this->addRelatedEntity($entity);

        return $this;
    }

    /**
     * Add a related entity.
     *
     * @param mixed $entity
     *
     * @return $this
     */
    public function addRelatedEntity($entity)
    {
        $exist = false;
        foreach ($this->persist_entities as $existing_entity) {
            if ($entity === $existing_entity) {
                $exist = true;
                break;
            }
        }

        if (!$exist) {
            $this->persist_entities[] = $entity;
        }

        return $this;
    }

    /**
     * Returns a collection of Doctrine entities.
     *
     * @return array
     */
    public function getPersistEntities()
    {
        return $this->persist_entities;
    }

    /**
     * Add an import map.
     *
     * @param OidEntityMap $entity_map
     *
     * @return $this
     */
    public function addImportMapEntity(OidEntityMap $entity_map)
    {
        $this->import_map_entities[] = $entity_map;

        return $this;
    }

    /**
     * Returns a collection of import maps.
     *
     * @return OidEntityMap[]
     */
    public function getImportMapEntities()
    {
        return $this->import_map_entities;
    }
}
