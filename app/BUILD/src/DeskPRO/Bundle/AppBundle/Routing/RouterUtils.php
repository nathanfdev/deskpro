<?php

namespace DeskPRO\Bundle\AppBundle\Routing;

use Symfony\Component\Routing\RouterInterface;

class RouterUtils
{
    private function __construct()
    {
    }

    /**
     * Use this to get the "real" router.
     *
     * This is generally discouraged
     * because it means your code isn't using the RouterInterface
     * directly, which means your code may need to be updated any
     * time we change something.
     *
     * @param RouterInterface $router
     *
     * @return RouterInterface
     */
    public static function unwrapDecoratedRouter(RouterInterface $router)
    {
        while ($router instanceof RouterDecorator) {
            $router = $router->getBaseRouter();
        }

        return $router;
    }
}
