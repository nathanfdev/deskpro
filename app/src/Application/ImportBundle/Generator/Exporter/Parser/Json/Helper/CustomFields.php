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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json\Helper;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserFormatterHelper;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;

/**
 * Class CustomFields.
 */
class CustomFields extends AbstractParserFormatterHelper
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
     * @param array $custom_fields
     *
     * @return Entity\CustomField[]
     */
    public function export(array $custom_fields)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($custom_fields)
            ->setPrefix('JSONCustomField')
            ->setRefColumn('oid')
            ->setMethod('exportCustomField')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a custom field entity.
     *
     * @param array $data
     *
     * @return Entity\CustomField|null
     */
    protected function exportCustomField(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'oid'         => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => 'custom_field_',
                'ref'     => 'oid',
            )),
            'key'   => TransformerInterface::TYPE_STRING,
            'value' => TransformerInterface::TYPE_STRING,
        ));

        $entity = new Entity\CustomField();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
            ->setKey($formatted['key'])
            ->setValue($formatted['value'])
        ;

        return $entity;
    }
}
