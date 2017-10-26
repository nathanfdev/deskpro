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

namespace DeskPRO\Bundle\AppBundle\ObjectAlias;

class Name
{
    /** @var string */
    private $value;

    /** @var array  */
    private $qualifiers;

    /**
     * @param string|Name $name
     * @return bool
     */
    public static function isValidIdentifier($name)
    {
        $identifier = $name instanceof Name ? $name->getIdentifier() : $name;

        $pattern = '#^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*#';
        return 1 === preg_match($pattern, $identifier);
    }

    /**
     * @param string $value
     * @param array $qualifiers
     */
    public function __construct($value, array $qualifiers)
    {
        $this->value = $value;
        $this->qualifiers = $qualifiers;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->value;
    }

    /**
     * @return array|string[]
     */
    public function getQualifiers()
    {
        return array_merge([], $this->qualifiers);
    }

    public function isQualified()
    {
        return !empty($this->qualifiers);
    }
}

