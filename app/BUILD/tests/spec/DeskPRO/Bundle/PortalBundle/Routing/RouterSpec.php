<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Routing;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use PhpSpec\ObjectBehavior;
use Symfony\Bundle\FrameworkBundle\Routing\Router as WrappedRouter;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Routing\PortalRouter
 */
class RouterSpec extends ObjectBehavior
{
    public function let(
        WrappedRouter $base_router,
        LanguageManager $language_manager,
        PortalModeStorage $mode_storage
    ) {
        $this->beConstructedWith($base_router, $language_manager, $mode_storage);
    }
}
