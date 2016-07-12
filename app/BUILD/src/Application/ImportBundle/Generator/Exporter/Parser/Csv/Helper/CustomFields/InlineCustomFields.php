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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv\Helper\CustomFields;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserHelper;

/**
 * Class InlineCustomFields.
 */
class InlineCustomFields extends AbstractParserHelper
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return 'inline_'.Entity\EntityInterface::TYPE_CUSTOM_FIELD;
    }

    /**
     * Returns a collection of inline custom fields.
     *
     * @param string $destination
     * @param array  $data
     *
     * @return Entity\Collection
     */
    public function export($destination, array $data)
    {
        $collection    = new Entity\Collection();
        $custom_fields = $this->parse($data);

        foreach ($custom_fields as $num => $custom_field) {
            $data = new Entity\CustomField();
            $data
                ->setRawData($custom_field)
                ->setOid($custom_field['property'])
                ->setDestination($destination)
                ->setKey($custom_field['field_name'])
                ->setValue($custom_field['value'])
            ;

            $collection->attach($data);
        }

        return $collection;
    }

    /**
     * Parses inline custom fields from entity.
     *
     * @param array $data
     *
     * @return array
     */
    private function parse(array $data)
    {
        $properties    = array_keys($data);
        $custom_fields = [];

        foreach ($properties as $property) {
            if (preg_match('/^custom "([^"]+)"$/', $property, $matches)) {
                $custom_fields[] = [
                    'property'   => $property,
                    'field_name' => $matches[1],
                    'value'      => $data[$property],
                ];
            }
        }

        return $custom_fields;
    }
}
