<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Theme;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandTheme;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Theme\ThemeView
 */
class ThemeViewSpec extends ObjectBehavior
{
    public function let(
        BrandStack $brand_stack,
        Brand $brand,
        BrandContainer $brand_container,
        PortalBrandThemeLoader $portalBrandThemeLoader,
        PortalBrandTheme $portalBrandTheme,
        ThemeInterface $theme,
        Tag $tag
    ) {
        $brand_stack->getActive()->willReturn($brand_container);
        $brand_container->getBrand()->willReturn($brand);

        $portalBrandThemeLoader->getPortalBrandTheme($brand)->willReturn($portalBrandTheme);
        $portalBrandTheme->getActiveTheme()->willReturn($theme);
        $theme->resolveTag('tag_name')->willReturn($tag);
        $tag->getDefinedOptions()->willReturn(['a', 'b', 'c', 'd', 'e']);

        $this->beConstructedWith($brand_stack, $portalBrandThemeLoader, [
            'a' => 'default',
            'd' => 'the controller sets these page defaults',
        ]);
    }

    public function it_will_call_a_tag_using_the_constructed_default_options(
        PortalBrandTheme $portalBrandTheme,
        $default_options
    ) {
        $portalBrandTheme->renderTag('tag_name', [
            'a' => 'default',
            'd' => 'the controller sets these page defaults',
        ])->shouldBeCalled();

        $no_explicit_options = [[]]; // uses first arg in the array (because twig, see commnets in class)

        $this->__call('tag_name', $no_explicit_options);
    }

    public function it_allows_tags_to_be_called_with_explicit_options_that_will_override_default_options(
        PortalBrandTheme $portalBrandTheme,
        $default_options
    ) {
        $portalBrandTheme->renderTag('tag_name', [
            'a' => 'NEW VAL',
            'b' => 'something',
            'd' => 'the controller sets these page defaults',
        ])->shouldBeCalled();

        $this->__call('tag_name', [[ // uses first arg in the array (because twig, see commnets in class)
            'a' => 'NEW VAL',
            'b' => 'something',
        ]]);
    }
}
