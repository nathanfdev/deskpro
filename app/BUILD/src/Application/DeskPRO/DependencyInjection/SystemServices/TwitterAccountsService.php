<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\TwitterAccounts\TwitterAccounts;

class TwitterAccountsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new TwitterAccounts($container->getEm());

        return $x;
    }
}
