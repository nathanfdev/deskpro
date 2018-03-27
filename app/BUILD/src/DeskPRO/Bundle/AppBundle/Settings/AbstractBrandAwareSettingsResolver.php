<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\Entity\Brand;

/**
 * Class AbstractBrandAwareSettingsResolver.
 */
abstract class AbstractBrandAwareSettingsResolver
{
    /**
     * @var BrandAwareSettingsResolver
     */
    protected $settingsResolver;

    /**
     * Constructor.
     *
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(BrandAwareSettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @param string $name
     * @param Brand  $brand
     *
     * @return mixed
     */
    protected function getSetting($name, Brand $brand = null)
    {
        return $this->settingsResolver->getSetting($name, $brand);
    }
}
