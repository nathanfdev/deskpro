<?php

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
