<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Community\CommunityForums;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class CommunityForumsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new CommunityForums($container->getEm());

        return $x;
    }
}
