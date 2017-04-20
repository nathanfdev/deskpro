<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

/**
 * Class RequestMatcher.
 */
class RequestMatcher
{
    /**
     * @var RouterWithDynamicContext
     */
    private $router;

    /**
     * Constructor.
     *
     * @param RouterWithDynamicContext $router
     */
    public function __construct(RouterWithDynamicContext $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $url
     * @param string $method
     *
     * @return bool
     */
    public function routeExists($url, $method)
    {
        return $this->requestRouteExist(Request::create($url, $method));
    }

    /**
     * @param Request $checkRequest
     *
     * @return bool
     */
    public function requestRouteExist(Request $checkRequest)
    {
        $originalContext = $this->router->getContext();
        $checkContext    = new RequestContext($checkRequest->getUri(), $checkRequest->getMethod());

        try {
            $this->router->setContext($checkContext);
            $this->router->matchRequest($checkRequest);

            return true;
        } catch (\Exception $e) {
            return false;
        } finally {
            $this->router->setContext($originalContext);
        }
    }
}
