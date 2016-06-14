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

namespace Application\ImportBundle\Generator\Exporter\Parser\DeskPRO;

use Application\DeskPRO\Entity\ImportMap;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;

/**
 * Class PeopleCustomDef.
 */
final class PeopleCustomDef extends AbstractCustomDefParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_PERSON_CUSTOM_DEF;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return count($this->reader->findCustomDefPeople());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->findCustomDefPeople())
            ->setPrefix('DPCustomDefPerson')
            ->setRefColumn('id')
            ->setMethod('exportCustomDef')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCustomDefEntity()
    {
        return new Entity\PersonCustomDef();
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportMapKey()
    {
        return ImportMap::TYPE_DESKPRO_USER_FIELD;
    }
}
