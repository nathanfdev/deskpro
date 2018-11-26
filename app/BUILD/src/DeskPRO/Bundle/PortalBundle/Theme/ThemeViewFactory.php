<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use Symfony\Component\HttpFoundation\Request;

class ThemeViewFactory
{
    /**
     * @var \DeskPRO\Bundle\BrandBundle\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @var PortalBrandThemeLoader
     */
    private $brand_theme_loader;

    public function __construct(BrandStack $brand_stack, PortalBrandThemeLoader $brand_theme_loader)
    {
        $this->brand_stack        = $brand_stack;
        $this->brand_theme_loader = $brand_theme_loader;
    }

    public function createView(array $options = [])
    {
        return new ThemeView($this->brand_stack, $this->brand_theme_loader, $options);
    }

    public function createViewFromRequest(Request $request, array $options = [])
    {
        return $this->createView($options);
    }
}
