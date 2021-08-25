<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

use Symfony\Component\DependencyInjection\Container;

class ZippyFactory
{
    /**
     * @param Container $container
     *
     * @return Zippy
     */
    public static function getZippy(Container $container)
    {
        $zippy = Zippy::load();

        return $zippy;
    }
}
