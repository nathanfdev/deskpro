<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Languages\Detect;

class LanguageDetectService
{
    public static function create(DeskproContainer $container)
    {
        $detect = new Detect($container->getDataService('Language'));

        return $detect;
    }
}
