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
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;

class BrandContext extends BaseContext
{
    /**
     * @Given I have several brands
     */
    public function iHaveSeveralBrand()
    {
        $brand = $this->findOtherBrand();

        expect($brand)->notToBe(null);
    }

    /**
     * @Then I remember default brand id
     */
    public function getDefaultBrandId()
    {
        $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        DataContext::setPlaceholder('defaultBrandId', $brandId);

        return $brandId;
    }

    /**
     * @Then I remember default brand
     */
    public function getDefaultBrand()
    {
        $brandId = $this->getDefaultBrandId();

        $brand = $this->findBrand($brandId);

        DataContext::setReference('defaultBrand', $brand);

        return $brand;
    }

    /**
     * @return Brand
     */
    protected function findOtherBrand()
    {
        /** @var Brand[] $brands */
        $brands = $this->repository(Brand::class)->findAll();

        foreach ($brands as $brand) {
            if ($brand->getId() != $this->getDefaultBrandId()) {
                DataContext::setPlaceholder('otherBrandId', $brand->getId());

                return $brand;
            }
        }

        $brand = $this->createNewBrand();

        DataContext::setPlaceholder('otherBrandId', $brand->getId());

        return $brand;
    }

    /**
     * @return Brand
     */
    protected function createNewBrand()
    {
        $brand = new Brand();

        $themeSet = new ThemeSet();
        $themeSet->setThemeId('Standard');
        $this->em()->persist($themeSet);

        $editThemeSet = new ThemeSet();
        $editThemeSet->setThemeId('Standard');
        $this->em()->persist($editThemeSet);

        $brand->setName('Other brand');
        $this->em()->persist($brand);
        $this->em()->flush();

        return $brand;
    }

    /**
     * @param int $brandId
     *
     * @return Brand
     */
    protected function findBrand($brandId)
    {
        return $this->repository(Brand::class)->find($brandId);
    }
}
