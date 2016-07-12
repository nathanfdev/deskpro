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

namespace Application\ImportBundle\Generator\Exporter\Parser\DeskPRO\Helper;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserHelper;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class CustomFields.
 */
class CustomFields extends AbstractParserHelper
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_CUSTOM_FIELD;
    }

    /**
     * Exports custom fields.
     *
     * @param ArrayCollection|array $custom_fields
     *
     * @return Entity\CustomField[]
     */
    public function export($custom_fields)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($custom_fields)
            ->setPrefix('DPCustomField')
            ->setRefColumn('id')
            ->setMethod('exportCustomField')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns custom field entity.
     *
     * @param DeskPROEntity\CustomDataAbstract $custom_data
     *
     * @return Entity\CustomField
     */
    protected function exportCustomField(DeskPROEntity\CustomDataAbstract $custom_data)
    {
        $value = $custom_data->getData();
        if ($custom_data->root_field->isChoiceType()) {
            $value = $custom_data->field->getRealTitle();
        }

        $entity = new Entity\CustomField();
        $entity
            ->setRawData($custom_data->toApiData())
            ->setOid($custom_data->getId())
            ->setDestination($entity->getDestinationPrefix().$custom_data->getId())
            ->setKey($custom_data->root_field->getRealTitle())
            ->setValue($value)
        ;

        return $entity;
    }
}
