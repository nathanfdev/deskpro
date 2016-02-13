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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv\Helper\CustomFields;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserFormatterHelper;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;

/**
 * Class MultipleCustomFields.
 */
class MultipleCustomFields extends AbstractParserFormatterHelper
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return 'multiple_'.Entity\EntityInterface::TYPE_CUSTOM_FIELD;
    }

    /**
     * Returns a collection of custom fields.
     *
     * @param array  $data
     * @param string $destination_prefix
     * @param string $ref_column
     *
     * @return Entity\CustomField[]|Entity\Collection
     */
    public function export(array $data, $destination_prefix, $ref_column)
    {
        $that   = $this;
        $config = new ExportCollectionConfig();
        $config
            ->setData($data)
            ->setPrefix('CSVCustomField')
            ->setRefColumn($ref_column)
            ->setMethod(function ($data, $num) use ($that, $destination_prefix, $ref_column) {
                return $that->exportCustomField($num, $destination_prefix, $data, $ref_column);
            })
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns an attachment entity.
     *
     * @param int    $num
     * @param string $destination_prefix
     * @param array  $data
     * @param string $ref_column
     *
     * @return Entity\CustomField
     */
    public function exportCustomField($num, $destination_prefix, array $data, $ref_column)
    {
        if (isset($data[$ref_column])) {
            $data['destination'] = $destination_prefix.$data[$ref_column];
        }

        $formatted = $this->formatter->format($data, array(
            $ref_column   => TransformerInterface::TYPE_STRING,
            'destination' => TransformerInterface::TYPE_DESTINATION,
            'field_name'  => TransformerInterface::TYPE_STRING,
            'value'       => TransformerInterface::TYPE_STRING,
        ));

        $entity = new Entity\CustomField();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($num)
            ->setKey($formatted['field_name'])
            ->setValue($formatted['value'])
        ;

        return $entity;
    }
}
