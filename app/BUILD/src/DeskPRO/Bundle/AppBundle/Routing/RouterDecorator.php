<?php

namespace DeskPRO\Bundle\AppBundle\Routing;

use Symfony\Component\Routing\RouterInterface;

interface RouterDecorator
{
    /**
     * @return RouterInterface
     */
    public function getBaseRouter();
}
