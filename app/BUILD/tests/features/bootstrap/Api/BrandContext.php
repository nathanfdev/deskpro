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

namespace DpBehat\Api;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;

/**
 * Class BrandContext.
 */
class BrandContext extends BaseContext
{
    /**
     * @Given I have several brands
     */
    public function iHaveSeveralBrand()
    {
        $brand = $this->createNewBrand('Other Brand');
        DataContext::setPlaceholder('otherBrandId', $brand->getId());
    }

    /**
     * @Then I have default brand
     */
    public function iHaveDefaultBrand()
    {
        $brand = $this->createNewBrand('default');

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingRepo */
        $settingRepo = $this->em()->getRepository(Setting::class);
        $settingRepo->updateSetting('portal.default_brand', $brand->getId());
        $this->get('settings_resolver')->getGlobalSettings(true);

        DataContext::setReference('defaultBrand', $brand);
        DataContext::setPlaceholder('defaultBrandId', $brand->getId());

        return $brand;
    }

    /**
     * @param string $name
     *
     * @return Brand
     */
    protected function createNewBrand($name)
    {
        $brand = new Brand();

        $themeSet = new ThemeSet();
        $themeSet->setThemeId('standard');
        $this->em()->persist($themeSet);

        $editThemeSet = new ThemeSet();
        $editThemeSet->setThemeId('standard');
        $this->em()->persist($editThemeSet);

        $brand->setName($name);
        $brand->setThemeSet($themeSet);
        $brand->setEditThemeSet($editThemeSet);

        $this->em()->persist($brand);
        $this->em()->flush();

        return $brand;
    }
}
