<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Community\CustomCommunityChannels;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class CustomCommunityChannelsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new CustomCommunityChannels($container->getEm());

        return $x;
    }
}
