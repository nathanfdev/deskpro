<?php

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
