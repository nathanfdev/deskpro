<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use Symfony\Component\HttpFoundation\Request;

class ThemeViewFactory
{
    /**
     * @var BrandStack
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
