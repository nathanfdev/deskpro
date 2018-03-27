<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Parser;

use Nelmio\ApiDocBundle\DataTypes;

/**
 * Class NestedType.
 */
class NestedType
{
    const ENTITY_COLLECTION     = 'entity_collection';
    const ENTITY                = 'entity';
    const STRING_REP_COLLECTION = 'string_collection';
    const STRING_REP            = 'string_rep';
    const CUSTOM_DATA           = 'custom_data';

    /**
     * @var array
     */
    protected static $templates = [
        self::ENTITY                => 'integer id (%s)',
        self::ENTITY_COLLECTION     => 'array of integer ids (%s)',
        self::STRING_REP            => 'string representation (%s)',
        self::STRING_REP_COLLECTION => 'array of string representations (%s)',
        self::CUSTOM_DATA           => 'dynamically declared custom fields (%s)',
    ];

    /**
     * @var array
     */
    protected static $type_mapping = [
        self::ENTITY                => DataTypes::INTEGER,
        self::ENTITY_COLLECTION     => DataTypes::COLLECTION,
        self::STRING_REP            => DataTypes::STRING,
        self::STRING_REP_COLLECTION => DataTypes::COLLECTION,
        self::CUSTOM_DATA           => DataTypes::COLLECTION,
    ];

    /**
     * @var
     */
    protected $name;

    /**
     * @var
     */
    protected $type;

    /**
     * NestedType constructor.
     *
     * @param $name
     * @param $type
     */
    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function composeResponse()
    {
        return  [
            'class'      => $this->name,
            'primitive'  => true,
            'inline'     => false,
            'normalized' => $this->fillTemplate(),
            'actualType' => $this->getActualType(),
        ];
    }

    /**
     * @return mixed
     */
    protected function getBaseName()
    {
        $parts = explode('\\', $this->name);

        return end($parts);
    }

    /**
     * @return string
     */
    protected function fillTemplate()
    {
        return sprintf(self::$templates[$this->type], $this->getBaseName());
    }

    /**
     * @return mixed
     */
    protected function getActualType()
    {
        return self::$type_mapping[$this->type];
    }
}
