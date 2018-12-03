<?php

namespace DeskPRO\Bundle\AppBundle\Request;

use DeskPRO\Bundle\AppBundle\EventListener\RequestTypeClassifierListener;
use Symfony\Component\HttpFoundation\Request;

class RequestUtils
{
    private function __construct()
    {
    }

    /**
     * Checks is the request is marked as a low-level request. These types of requests
     * often do not have the usual information set up (such as a brand stack initiated to the current URL).
     *
     * The framework reaches the controller without a database connection.
     *
     * @see RequestTypeClassifierListener
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function isLowRequest(Request $request)
    {
        return $request->attributes->has('_dp_is_low');
    }

    /**
     * Checks if the request is marked as an 'api' request. These types of requests
     * are often excluded from things like language prefix redirects.
     *
     * @see RequestTypeClassifierListener
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function isPortalApiRequest(Request $request)
    {
        return $request->attributes->has('_dp_is_portal_api');
    }

    /**
     * Checks if the request is a system request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function isSysRequest(Request $request)
    {
        return stripos($request->getRequestUri(), '/sys/') === 0;
    }

    /**
     * Checks if the request is a proxy request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function isProxyRequest(Request $request)
    {
        return stripos($request->getRequestUri(), '/_proxy/') === 0;
    }
}
