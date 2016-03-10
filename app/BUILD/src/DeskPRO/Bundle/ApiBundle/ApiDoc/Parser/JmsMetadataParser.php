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

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Parser;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use JMS\Serializer\Metadata\PropertyMetadata;
use Nelmio\ApiDocBundle\DataTypes;

/**
 * Class JmsMetadataParser.
 */
class JmsMetadataParser extends \Nelmio\ApiDocBundle\Parser\JmsMetadataParser
{
    /**
     * It's use to handle our super-trouper custom type called "entity"
     * So if it's entity array - then in doc you'll see something like
     * "array of interger ids (EntityName)".
     * In case it's not an array, but entity - then you'll see text like below:
     * "integer id (EntityName)"
     * Everybody dance now!
     *
     * @param PropertyMetadata $item
     *
     * @return array
     */
    protected function processDataType(PropertyMetadata $item)
    {
        // check for a type inside something that could be treated as an array
        if ($nestedType = $this->checkIfDpCustomEntity($item)) {
            $return = [
                'class'     => $nestedType['name'],
                'primitive' => true,
                'inline'    => false,
            ];
            $parts     = explode('\\', $nestedType['name']);
            $base_name = end($parts);
            if ($item->type['name'] === EntityInterface::SERIALIZER_TYPE) {
                return $return + [
                            'normalized' => sprintf('integer id (%s)', $base_name),
                            'actualType' => DataTypes::COLLECTION,
                        ];
            } else {
                return $return + [
                            'normalized' => sprintf('array of integer ids (%s)', $base_name),
                            'actualType' => DataTypes::INTEGER,
                        ];
            }
        } else {
            return parent::processDataType($item);
        }
    }

    /**
     * @param PropertyMetadata $item
     *
     * @return array|bool
     */
    protected function checkIfDpCustomEntity(PropertyMetadata $item)
    {
        if (isset($item->type['name']) && in_array($item->type['name'], array('array', 'ArrayCollection'))) {
            if (
                isset($item->type['params'][0]['name'])
                && $item->type['params'][0]['name'] === EntityInterface::SERIALIZER_TYPE
                // OMG!
                && isset($item->type['params'][0]['params'][0]['name'])
            ) {
                $type = $item->type['params'][0]['params'][0];

                return $type;
            }

            return false;
        } elseif (
            isset($item->type['name'])
            && $item->type['name'] === EntityInterface::SERIALIZER_TYPE
            && isset($item->type['params'][0]['name'])
        ) {
            return [
                'name' => $item->type['params'][0]['name'],
            ];
        }

        return false;
    }
}
