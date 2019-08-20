<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Community\CommunityCategories;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class CustomCommunityChannelsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new CommunityCategories($container->getEm());

        return $x;
    }
}
