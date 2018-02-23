<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

class QualifiedName
{
    /** @var string */
    private $localName;

    /** @var array  */
    private $qualifiers;

    const QUALIFIER_SEPARATOR = ':';

    /**
     * @param QualifiedName $name
     * @return bool
     */
    public static function isValidIdentifier(QualifiedName $name)
    {
        $pattern = '#^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*#';

        $names = array_merge(
            [ $name->getLocalName() ],
            $name->getQualifiers()
        );

        $valid = true;
        for ($i =0; $i < count($names) && $valid === true; $i++ ) {
            $valid = 1 === preg_match($pattern, $names[$i]);
        }

        return $valid;
    }

    /**
     * @param string $localName
     * @param array|string[] $qualifiers
     */
    public function __construct($localName, array $qualifiers)
    {
        $this->localName = $localName;
        $this->qualifiers = $qualifiers;
    }

    /**
     * @return string
     */
    public function getLocalName()
    {
        return $this->localName;
    }

    /**
     * @return array|string[]
     */
    public function getQualifiers()
    {
        return $this->qualifiers;
    }

    public function hasQualifiers()
    {
        return !empty($this->qualifiers);
    }

    /**
     * @return array|string[]
     */
    public function toList()
    {
        return array_merge( $this->qualifiers, [$this->localName]);
    }
}

