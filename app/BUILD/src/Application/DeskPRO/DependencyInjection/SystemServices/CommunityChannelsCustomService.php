<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Community\CommunityChannelsCustom;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class CommunityChannelsCustomService
{
    public static function create(DeskproContainer $container)
    {
        $x = new CommunityChannelsCustom($container->getEm());

        return $x;
    }
}
