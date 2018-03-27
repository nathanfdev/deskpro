<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\FeedbackStatuses\FeedbackStatuses;

class FeedbackStatusesService
{
    public static function create(DeskproContainer $container)
    {
        $x = new FeedbackStatuses($container->getEm());

        return $x;
    }
}
