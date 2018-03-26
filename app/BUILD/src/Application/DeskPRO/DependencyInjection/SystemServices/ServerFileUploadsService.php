<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\ServerFileUploads\ServerFileUploads;

class ServerFileUploadsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new ServerFileUploads($container->getEm());

        return $x;
    }
}
