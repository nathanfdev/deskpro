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

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use DeskPRO\Bundle\AppBundle\ObjectAlias;

class FullyQualifiedAppName
{
    /**
     * @param FullyQualifiedAppName $qualifier
     * @return array
     */
    public static function toArray( FullyQualifiedAppName $qualifier)
    {
        return ['app', $qualifier->getId()];
    }

    /**
     * @param array $qualifier
     * @return FullyQualifiedAppName|null
     */
    public static function parseArray( array $qualifier)
    {
        if (count($qualifier) === 3 && $qualifier[0] === 'app') {
            $id = (integer) $qualifier[1];
            if ($qualifier[1] === (string) $id && is_string($qualifier[2]) && !empty($qualifier[2]) ) {
                return new FullyQualifiedAppName($qualifier[1], $qualifier[2]);
            }
        }

        return null;
    }

    /**
     * @param ObjectAlias\QualifiedName $qualifiedName
     * @return FullyQualifiedAppName|null
     */
    public static function parseName( ObjectAlias\QualifiedName $qualifiedName)
    {
        return FullyQualifiedAppName::parseArray($qualifiedName->toList());
    }

    /**
     * @param string $id
     * @param string $localName
     */
    public function __construct($id, $localName)
    {
        $this->id = $id;
        $this->localName = $localName;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocalName()
    {
        return $this->localName;
    }

    /**
     * @param $other
     * @return bool
     */
    public function equals($other)
    {
        return $other instanceof FullyQualifiedAppName
            && $other->getId()  === $this->getId()
            && $other->getLocalName() === $this->getLocalName()
        ;
    }
}
