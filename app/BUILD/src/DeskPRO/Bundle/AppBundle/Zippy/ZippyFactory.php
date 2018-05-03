<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

use Alchemy\Zippy;
use Symfony\Component\DependencyInjection\Container;

class ZippyFactory
{
    /**
     * @param Container $container
     *
     * @return Zippy\Zippy
     */
    public static function getZippy(Container $container)
    {
        $zippy = Zippy\Zippy::load();

        return $zippy;
    }
}
