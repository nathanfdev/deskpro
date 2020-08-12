<?php

namespace DeskPRO\Bundle\ApiBundle\Rest\Response;

use FOS\RestBundle\Response\AllowedMethodsLoader\AllowedMethodsRouterLoader as BaseAllowedMethodsRouterLoader;

/**
 * Class AllowedMethodsRouterLoader
 *
 * @package DeskPRO\Bundle\ApiBundle\Rest\Response
 */
class AllowedMethodsRouterLoader extends BaseAllowedMethodsRouterLoader
{
    public function isOptional()
    {
        return false;
    }
}
