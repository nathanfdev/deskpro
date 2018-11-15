<?php

namespace DeskPRO\Bundle\BrandBundle\Theme;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\BrandBundle\Brand\BrandContainerFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver;

/**
 * Class PortalBrandThemeLoader.
 */
class PortalBrandThemeLoader
{
    /**
     * @var \DeskPRO\Bundle\BrandBundle\Brand\BrandContainerFactory
     */
    private $brandContainerFactory;

    /**
     * @var ThemeResolver
     */
    private $themeResolver;

    /**
     * @var PortalModeStorage
     */
    private $portalModeStorage;

    /**
     * @var PortalBrandTheme[]
     */
    private $instances = [];

    /**
     * Constructor.
     *
     * @param BrandContainerFactory $brandContainerFactory
     * @param ThemeResolver         $themeResolver
     * @param PortalModeStorage     $portalModeStorage
     */
    public function __construct(BrandContainerFactory $brandContainerFactory, ThemeResolver $themeResolver, PortalModeStorage $portalModeStorage)
    {
        $this->brandContainerFactory = $brandContainerFactory;
        $this->themeResolver         = $themeResolver;
        $this->portalModeStorage     = $portalModeStorage;
    }

    /**
     * @param Brand $brand
     *
     * @return PortalBrandTheme
     */
    public function getPortalBrandTheme(Brand $brand)
    {
        $id = $brand->getId();

        if (!isset($this->instances[$id])) {
            $this->instances[$id] = new PortalBrandTheme(
                $this->brandContainerFactory->create($brand),
                $this->themeResolver,
                $this->portalModeStorage
            );
        }

        return $this->instances[$id];
    }

    public function getPortalModeStorage()
    {
        return $this->portalModeStorage;
    }
}
