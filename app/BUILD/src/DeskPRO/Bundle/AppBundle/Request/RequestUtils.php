<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
}
