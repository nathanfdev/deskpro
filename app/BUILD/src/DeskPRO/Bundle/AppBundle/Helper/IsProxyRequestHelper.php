<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Helper;

use Symfony\Component\HttpFoundation\Request;

class IsProxyRequestHelper
{
    public static function check(Request $request)
    {
        return '/_proxy' === substr($request->getPathInfo(), 0, 7);
    }
}
