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

namespace DeskPRO\Bundle\AuditBundle\Entity\NamingStrategy;

use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\AuditBundle\Log\AuditLog;

/**
 * Class ThemeNamingStrategy.
 */
class ThemeNamingStrategy implements NamingStrategyInterface
{
    /**
     * @param          $object
     * @param AuditLog $log
     *
     * @return string
     */
    public function getName($object, AuditLog $log)
    {
        $name = '';
        if ($object instanceof ThemeSetAsset || $object instanceof Template) {
            /** @var ThemeSetAsset $object */
            if ($brand = $object->getThemeSet()->getBrand()) {
                $name = $brand->getName().' (Brand) - '.$object->getThemeSet()->getThemeId();
            } elseif ($brand2 = $object->getThemeSet()->getBrand2()) {
                $name = $brand2->getName().' (Brand) - Preview '.$object->getThemeSet()->getThemeId();
            }

            $tags = $object->getTags();
            $name .= $tags ? ' ('.array_pop($tags).')' : '';
        } elseif ($object instanceof ThemeSet) {
            if ($brand = $object->getBrand()) {
                $name = $brand->getName().' (Brand) - '.$object->getThemeId();
            } elseif ($brand2 = $object->getBrand2()) {
                $name = $brand2->getName().' (Brand) - Preview '.$object->getThemeId();
            }
        }

        return $name;
    }
}
