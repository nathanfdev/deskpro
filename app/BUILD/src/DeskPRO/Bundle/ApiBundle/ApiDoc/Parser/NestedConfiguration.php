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

/**
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
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

use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;

/**
 * Class NestedConfiguration.
 */
class NestedConfiguration
{
    /**
     * @var string
     */
    protected $collection_type;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $serializer_type;

    /**
     * NestedConfiguration constructor.
     *
     * @param string $type
     * @param string $collection_type
     * @param string $serializer_type
     */
    protected function __construct($type, $collection_type, $serializer_type)
    {
        $this->type            = $type;
        $this->collection_type = $collection_type;
        $this->serializer_type = $serializer_type;
    }

    /**
     * @param $type
     *
     * @return static
     */
    public static function getConfig($type)
    {
        switch ($type) {
            case SerializerTypes::TYPE_ENTITY:
                return new static(NestedType::ENTITY, NestedType::ENTITY_COLLECTION, $type);
            case SerializerTypes::TYPE_TO_STRING:
                return new static(NestedType::STRING_REP, NestedType::STRING_REP_COLLECTION, $type);
            case SerializerTypes::TYPE_CUSTOM_DATA:
                return new static(NestedType::CUSTOM_DATA, NestedType::CUSTOM_DATA, $type);
            default:
                throw new \InvalidArgumentException();
        }
    }

    /**
     * @return string
     */
    public function getSerializerType()
    {
        return $this->serializer_type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getCollectionType()
    {
        return $this->collection_type;
    }
}
