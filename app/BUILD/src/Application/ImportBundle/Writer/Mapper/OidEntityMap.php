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

namespace Application\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Model\ImportModelInterface;

/**
 * Class OidEntityMap.
 */
class OidEntityMap
{
    /**
     * @var ImportModelInterface
     */
    private $entity;

    /**
     * @var mixed
     */
    private $record;

    /**
     * Constructor.
     *
     * @param ImportModelInterface $entity
     * @param mixed                $record
     */
    public function __construct(ImportModelInterface $entity, $record)
    {
        $this->entity = $entity;
        $this->record = $record;
    }

    /**
     * @return ImportModelInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return mixed
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @return DeskPROEntity\ImportMap
     */
    public function createDoctrineImportMapEntity()
    {
        if (!method_exists($this->record, 'getId') || !$this->record->getId()) {
            throw new \RuntimeException(sprintf('Unable to get record `%s` id', get_class($this->record)));
        }
        if (!$this->entity->getOid()) {
            throw new \RuntimeException('Empty entity oid');
        }

        $importMap = new DeskPROEntity\ImportMap();
        $importMap
            ->setTypename($this->entity->getImportMapKey())
            ->setOldId($this->entity->getOid())
            ->setNewId($this->record->getId())
        ;

        return $importMap;
    }
}
