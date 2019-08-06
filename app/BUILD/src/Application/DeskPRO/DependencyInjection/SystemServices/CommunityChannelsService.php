<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Community\CommunityChannels;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class CommunityChannelsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new CommunityChannels($container->getEm());

        return $x;
    }
}
