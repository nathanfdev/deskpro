<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\BrandBundle\Theme\PortalBrandThemeLoader;

class ThemeView
{
    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var PortalBrandThemeLoader
     */
    private $brand_theme_loader;

    /**
     * @var array
     */
    private $default_options;

    public function __construct(BrandStack $brand_stack, PortalBrandThemeLoader $brand_theme_loader, array $default_options)
    {
        $this->default_options    = $default_options;
        $this->brand_stack        = $brand_stack;
        $this->brand_theme_loader = $brand_theme_loader;
    }

    public function __call($tag_name, array $explicit_options)
    {
        $default_options = $this->calculateDefaultOptions($tag_name);

        if (is_array($explicit_options) && !empty($explicit_options[0])) {
            $explicit_options = $explicit_options[0];
        } else {
            $explicit_options = [];
        }

        $options = array_merge($default_options, $explicit_options);

        $brand_container = $this->brand_stack->getActive();
        $brand_theme     = $this->brand_theme_loader->getPortalBrandTheme($brand_container->getBrand());

        return $brand_theme->renderTag($tag_name, $options);
    }

    protected function calculateDefaultOptions($tag_name)
    {
        $options = [];

        $brand_container = $this->brand_stack->getActive();
        $brand_theme     = $this->brand_theme_loader->getPortalBrandTheme($brand_container->getBrand());
        $theme           = $brand_theme->getActiveTheme();

        if (!$tag = $theme->resolveTag($tag_name)) {
            throw new \InvalidArgumentException(sprintf('cannot resolve tag "%s"', $tag_name));
        }

        foreach ($tag->getDefinedOptions() as $defined) {
            if (array_key_exists($defined, $this->default_options)) {
                $options[$defined] = $this->default_options[$defined];
            }
        }

        return $options;
    }
}
