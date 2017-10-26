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

use DeskPRO\Bundle\AppBundle\ObjectAlias;

class Comparators
{
    /**
     * @param \DeskPRO\Bundle\AppBundle\ObjectAlias\ObjectAliasInterface $a
     * @param \DeskPRO\Bundle\AppBundle\ObjectAlias\ObjectAliasInterface $b
     * @return bool
     */
    public static function equal( ObjectAlias\ObjectAliasInterface $a, ObjectAlias\ObjectAliasInterface $b)
    {
        if ($a->getObjectId() !== $b->getObjectId()) {
            return false;
        }

        if ($a->getObjectType() !== $b->getObjectType()) {
            return false;
        }

        if ($a->getAlias() !== $b->getAlias()) {
            return false;
        }

        $aQualifiers = $a->getQualifiers();
        $bQualifiers = $b->getQualifiers();

        if (count($aQualifiers) !== count($bQualifiers)) {
            return false;
        }

        $aQualifiers = array_map('implode', $aQualifiers);
        $bQualifiers = array_map('implode', $bQualifiers);

        return array_diff($aQualifiers, $bQualifiers) === array_diff($bQualifiers, $aQualifiers);
    }

}
