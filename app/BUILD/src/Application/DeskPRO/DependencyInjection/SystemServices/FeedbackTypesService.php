<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\FeedbackTypes\FeedbackTypes;

class FeedbackTypesService
{
    public static function create(DeskproContainer $container)
    {
        $x = new FeedbackTypes($container->getEm());

        return $x;
    }
}
