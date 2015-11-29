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

use Application\DeskPRO\Entity\Brand as BrandEntity;
use Application\DeskPRO\EntityRepository\Brand;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Themes\Standard\StandardTheme;

/**
 * A serive that can quickly hand you the default brand (useful in cases here there is no request listener detecting
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
        try {
            return $this->brand_repo->find(
                $this->settings_resolver->getGlobalSettings()->get('portal.default_brand', 1)
            );
        } catch (\Exception $e) {
            // if somehow we don't have a database, just return a brand that represents a "standard theme"
            $b         = new BrandEntity();
            $b->id     = 1;
            $theme_set = new ThemeSet();
            $theme_set->setThemeId(StandardTheme::THEME_ID);
            $b->setThemeSet($theme_set);

            return $b;
        }
    }
}
