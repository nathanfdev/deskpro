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

namespace spec\DeskPRO\Bundle\PortalBundle\HttpKernel;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
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
