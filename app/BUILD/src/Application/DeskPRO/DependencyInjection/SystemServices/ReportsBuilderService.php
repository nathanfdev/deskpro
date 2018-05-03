<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Reports\Builder;

class ReportsBuilderService
{
    /**
     * @param DeskproContainer $container
     *
     * @return Builder
     */
    public static function create(DeskproContainer $container)
    {
        return new Builder($container->getEm());
    }
}
