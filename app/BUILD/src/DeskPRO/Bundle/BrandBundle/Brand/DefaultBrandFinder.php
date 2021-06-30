<?php

namespace DeskPRO\Bundle\BrandBundle\Brand;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Themes\Standard\StandardTheme;
use Doctrine\ORM\EntityManager;

/**
 * A service that can quickly hand you the default brand (useful in cases here there is no request listener detecting
 * the active brand on the stack (CLI).
 */
class DefaultBrandFinder
{
    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver
     */
    private $settingsResolver;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settingsResolver
     * @param EntityManager    $em
     */
    public function __construct(SettingsResolver $settingsResolver, EntityManager $em)
    {
        $this->settingsResolver = $settingsResolver;
        $this->em               = $em;
    }

    /**
     * @return Brand
     */
    public function getDefaultBrand()
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        // bit of a hack to get warmup of api docs working properly
        // brand stack used a lot in forms, and forms are read for api docs,
        // so we need a value here
        if ($DP_ENV && $DP_ENV->getRuntimeVar('is_building', false)) {
            $b = new Brand();
            $this->em->persist($b);
            return $b;
        }

        $brand = null;

        try {
            // get default brand from settings
            $defaultBrandId = $this->settingsResolver->getGlobalSettings()->get('portal.default_brand');
            if ($defaultBrandId) {
                $brand = $this->em->getRepository(Brand::class)->find($defaultBrandId);
            }

            // get first brand as fallback
            if (!$brand) {
                $brand = $this->em->getRepository(Brand::class)->findOneBy([]);
            }
        } catch (\Exception $e) {
        }

        return $brand;
    }

    /**
     * @return Brand
     */
    public function getDefaultBrandModel()
    {
        $brand = $this->getDefaultBrand();

        // if somehow we don't have a database or brand entity, just return a brand that represents a "standard theme"
        if (!$brand) {
            $brand     = new Brand();
            $brand->id = 1;
            $theme_set = new ThemeSet();
            $theme_set->setThemeId(StandardTheme::THEME_ID);
            $theme_set->setBrand($brand);
            $brand->setThemeSet($theme_set);
        }

        return $brand;
    }
}
