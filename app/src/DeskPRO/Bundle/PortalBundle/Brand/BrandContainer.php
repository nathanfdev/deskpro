<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Brand;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\NewSettings\SettingsBag;

/**
 * The BrandContainer is a hub that holds all of the information that might be needed in the system that relate to a
 * particular brand. It is the context of the brand in question. It's created by the BrandFactory.
 */
class BrandContainer
{
    /**
     * @var \Application\DeskPRO\Entity\Brand
     */
    private $brand;

    /**
     * @var \Application\DeskPRO\NewSettings\SettingsBag
     */
    private $settings;

    /**
     * @var BrandAssetLoader
     */
    private $asset_loader;

    public function __construct(
        Brand $brand,
        SettingsBag $settings,
        BrandAssetLoader $asset_loader
    ) {
        $this->brand        = $brand;
        $this->settings     = $settings;
        $this->asset_loader = $asset_loader;
    }

    /**
     * @param $setting_name
     * @param $default
     *
     * @return mixed
     */
    public function getSetting($setting_name, $default = null)
    {
        return $this->getSettings()->get($setting_name, $default);
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return BrandAssetLoader
     */
    public function getAssetLoader()
    {
        return $this->asset_loader;
    }

    /**
     * @return SettingsBag
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
