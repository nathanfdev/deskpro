<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\HttpKernel;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\BrandBundle\Brand\BrandContainer;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandTheme;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\HttpKernel\ControllerNameParser
 */
class ControllerNameParserSpec extends ObjectBehavior
{
    public function let(
        KernelInterface $kernel,
        BrandStack $brand_stack,
        PortalBrandThemeLoader $portalBrandThemeLoader
    ) {
        $this->beConstructedWith($kernel, $brand_stack, $portalBrandThemeLoader);
    }

    public function it_parses_theme_controller_names_into_actual_controllers(
        BrandStack $brand_stack,
        BrandContainer $brand_container,
        Brand $brand,
        PortalBrandThemeLoader $portalBrandThemeLoader,
        PortalBrandTheme $portalBrandTheme
    ) {
        $brand_stack->getActive()->willReturn($brand_container);
        $brand_container->getBrand()->willReturn($brand);

        $portalBrandThemeLoader->getPortalBrandTheme($brand)->willReturn($portalBrandTheme);
        $portalBrandTheme->resolveController('Theme:Articles:list')
            ->willReturn('DeskPRO\Bundle\PortalBundle\Themes\Base\Controller\ArticlesController::listAction');

        $this->parse('Theme:Articles:list')
            ->shouldReturn('DeskPRO\Bundle\PortalBundle\Themes\Base\Controller\ArticlesController::listAction');
    }
}
