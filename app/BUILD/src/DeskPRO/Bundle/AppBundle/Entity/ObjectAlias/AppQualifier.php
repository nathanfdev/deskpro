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

class AppQualifier
{
    /**
     * @param AppQualifier $qualifier
     * @return array
     */
    public static function toArray(AppQualifier $qualifier)
    {
        return ['app', $qualifier->getId()];
    }

    /**
     * @param array $qualifier
     * @return AppQualifier|null
     */
    public static function fromArray(array $qualifier)
    {
        if (count($qualifier) === 2 && $qualifier[0] === 'app') {
            $id = (integer) $qualifier[1];
            if ($qualifier[1] === (string) $id) {
                return new AppQualifier($qualifier[1]);
            }
        }

        return null;
    }

    /**
     * @param ObjectAlias\Name $name
     * @return AppQualifier|null
     */
    public static function fromName(ObjectAlias\Name $name)
    {
        if ($name->isQualified()) {
            return AppQualifier::fromArray($name->getQualifiers());
        }

        return null;
    }

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }
}
