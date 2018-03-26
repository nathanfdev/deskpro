<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\FeedbackCategories\FeedbackCategories;

class FeedbackCategoriesService
{
    public static function create(DeskproContainer $container)
    {
        $x = new FeedbackCategories($container->getEm());

        return $x;
    }
}
