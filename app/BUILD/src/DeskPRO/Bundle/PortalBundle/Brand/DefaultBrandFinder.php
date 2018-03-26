<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Brand;

use Application\DeskPRO\Entity\Brand as BrandEntity;
use Application\DeskPRO\EntityRepository\Brand;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Themes\Standard\StandardTheme;

/**
 * A service that can quickly hand you the default brand (useful in cases here there is no request listener detecting
 * the active brand on the stack (CLI).
 */
class DefaultBrandFinder
{
    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver
     */
    private $settings_resolver;

    /**
     * @var \Application\DeskPRO\EntityRepository\Brand
     */
    private $brand_repo;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settings_resolver
     * @param Brand            $brand_repo
     */
    public function __construct(SettingsResolver $settings_resolver, Brand $brand_repo)
    {
        $this->settings_resolver = $settings_resolver;
        $this->brand_repo        = $brand_repo;
    }

    /**
     * @return BrandEntity
     */
    public function getDefaultBrand()
    {
        $brand = null;
        try {
            $brand = $this->brand_repo->find(
                $this->settings_resolver->getGlobalSettings()->get('portal.default_brand', 1)
            );
        } catch (\Exception $e) {
        }

        // if somehow we don't have a database or brand entity, just return a brand that represents a "standard theme"
        if (!$brand) {
            $brand     = new BrandEntity();
            $brand->id = 1;
            $theme_set = new ThemeSet();
            $theme_set->setThemeId(StandardTheme::THEME_ID);
            $brand->setThemeSet($theme_set);
        }

        return $brand;
    }
}
