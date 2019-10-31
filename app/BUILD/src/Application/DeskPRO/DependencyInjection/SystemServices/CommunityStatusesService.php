<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Community\CommunityStatuses;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class CommunityStatusesService
{
    public static function create(DeskproContainer $container)
    {
        $x = new CommunityStatuses($container->getEm());

        return $x;
    }
}
