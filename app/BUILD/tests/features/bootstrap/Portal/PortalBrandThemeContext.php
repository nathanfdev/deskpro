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

namespace DpBehat\Portal;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DpBehat\Data\DataContext;

class PortalBrandThemeContext extends BasePortalContext
{
    /**
     * @Given the default brand is using the :theme_id theme
     *
     * @param string $themeId
     */
    public function theActiveBrandHasTheme($themeId)
    {
        $themeSet = $this->em()->getRepository(ThemeSet::class)->findOneBy(['theme_id' => $themeId]);

        // portal fixtures BC
        $brand = $this->getEm()->getRepository(Brand::class)->findOneBy(['theme_set' => $themeSet]);
        if ($brand) {
            return;
        }

        $brand = DataContext::getReference('defaultBrand');
        $brand->setThemeSet($themeSet);

        $this->em()->persist($brand);
        $this->em()->flush();
    }
}
