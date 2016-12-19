<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;

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
