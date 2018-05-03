<?php

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
     * Constructor.
     *
     * @param Brand       $brand
     * @param SettingsBag $settings
     */
    public function __construct(
        Brand $brand,
        SettingsBag $settings
    ) {
        $this->brand    = $brand;
        $this->settings = $settings;
    }

    /**
     * @param string $setting_name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getSetting($setting_name, $default = null)
    {
        return $this->getSettings()->get($setting_name, $default);
    }

    /**
     * This method used only for tests, where we need to reload settings at same thread!
     *
     * @internal
     *
     * @param SettingsBag $settings
     */
    public function setSettings(SettingsBag $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return SettingsBag
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
