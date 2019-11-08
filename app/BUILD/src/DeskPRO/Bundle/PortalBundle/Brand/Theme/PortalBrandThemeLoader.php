<?php

namespace DeskPRO\Bundle\PortalBundle\Brand\Theme;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\BrandBundle\Brand\BrandContainerFactory;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver;

/**
 * Class PortalBrandThemeLoader.
 */
class PortalBrandThemeLoader
{
    /**
     * @var BrandContainerFactory
     */
    private $brandContainerFactory;

    /**
     * @var BrandStack
     */
    private $brandStack;

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
     * @param BrandStack            $brandStack
     * @param ThemeResolver         $themeResolver
     * @param PortalModeStorage     $portalModeStorage
     */
    public function __construct(BrandContainerFactory $brandContainerFactory, BrandStack $brandStack, ThemeResolver $themeResolver, PortalModeStorage $portalModeStorage)
    {
        $this->brandContainerFactory = $brandContainerFactory;
        $this->themeResolver         = $themeResolver;
        $this->brandStack            = $brandStack;
        $this->portalModeStorage     = $portalModeStorage;
    }

    /**
     * @param Brand $brand
     *
     * @return PortalBrandTheme
     */
    public function getPortalBrandTheme(Brand $brand = null)
    {
        if (!$brand) {
            $brand = $this->brandStack->getActive()->getBrand();
        }
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
