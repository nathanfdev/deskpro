<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Theme\ThemeViewFactory
 */
class ThemeViewFactorySpec extends ObjectBehavior
{
    public function let(BrandStack $brand_stack, PortalBrandThemeLoader $portalBrandThemeLoader)
    {
        $this->beConstructedWith($brand_stack, $portalBrandThemeLoader);
    }

    public function it_creates_theme_view_objects_with_the_brand_stack_injected()
    {
        $view = $this->createView($options = ['options' => 'here']);

        $view->shouldHaveType('DeskPRO\Bundle\PortalBundle\Theme\ThemeView');
    }
}
