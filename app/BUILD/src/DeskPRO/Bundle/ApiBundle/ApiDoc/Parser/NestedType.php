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

    /**
     * @var array
     */
    protected static $templates = [
        self::ENTITY                => 'integer id (%s)',
        self::ENTITY_COLLECTION     => 'array of integer ids (%s)',
        self::STRING_REP            => 'string representation (%s)',
        self::STRING_REP_COLLECTION => 'array of string representations (%s)',
    ];

    /**
     * @var array
     */
    protected static $type_mapping = [
        self::ENTITY                => DataTypes::INTEGER,
        self::ENTITY_COLLECTION     => DataTypes::COLLECTION,
        self::STRING_REP            => DataTypes::STRING,
        self::STRING_REP_COLLECTION => DataTypes::COLLECTION,
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
