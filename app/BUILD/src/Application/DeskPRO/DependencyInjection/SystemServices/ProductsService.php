<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Products\Products;

class ProductsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new Products($container->getEm());
        $x->setDefaultProductPreference($container->getSetting('core.default_prod_id'));

        return $x;
    }
}
