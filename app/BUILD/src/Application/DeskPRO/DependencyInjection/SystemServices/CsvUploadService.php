<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CsvUpload\CsvUpload;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class CsvUploadService
{
    public static function create(DeskproContainer $container)
    {
        $x = new CsvUpload($container->getEm());

        return $x;
    }
}
