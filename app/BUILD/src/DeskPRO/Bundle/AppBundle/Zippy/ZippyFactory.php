<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

use Symfony\Component\DependencyInjection\Container;

class ZippyFactory
{
    /**
     * @param Container $container
     *
     * @return Zippy
     * @throws \Exception
     */
    public static function getZippy(Container $container)
    {
        $zippy = Zippy::load();

        /** @var ZipBombScanner $zipBombScanner */
        $zipBombScanner = $container->get('deskpro.zip_bomb_scanner');
        $zippy->setZipBombScanner($zipBombScanner);

        return $zippy;
    }
}
