<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Routing;

use DeskPRO\Bundle\AppBundle\Helper\IsProxyRequestHelper;
use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class PortalRequestInfo.
 */
class PortalRequestInfo
{
    public static $special_routes = [
        'saml_sls',
        'saml_metadata',
        'portal_agent_login',
        'user_saml_sls',
        'saml_sls',
        'user_saml_metadata',
        'saml_metadata',
        'portal_logout',
        'portal_login_usersource_sso',
        'portal_login_callback',
        'portal_login_authenticate',
        'portal_login_submit',
        'portal_apple_app_site_assoc',
        'dp_pagehit',
    ];

    /**
     * @var Request
     */
    private $request;

    /**
     * @var PortalMode
     */
    private $mode;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PortalModeFactory
     */
    private $mode_factory;

    /**
     * @param Request           $request
     * @param PortalMode        $mode
     * @param PortalModeFactory $mode_factory
     */
    public function __construct(Request $request, PortalMode $mode = null, PortalModeFactory $mode_factory)
    {
        $this->request      = $request;
        $this->mode         = $mode;
        $this->mode_factory = $mode_factory;
        // recommended you also call ->setRouter with the router service
    }

    /**
     * If a router is set on the object, it can do a better job at finding isSpecial() info.
     *
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router = null)
    {
        $this->router = $router;
    }

    /**
     * @return mixed
     */
    public function getLanguageUrlCode()
    {
        $pathinfo = $this->getRelevantPathInfo();

        $matcher = new UrlMatcher();
        $info    = $matcher->extractLanguageCode($pathinfo);

        return $info['lang_url_code'];
    }

    /**
     * @return mixed
     */
    public function getRoutablePath()
    {
        $pathinfo = $this->getRelevantPathInfo();

        $matcher = new UrlMatcher();
        $info    = $matcher->extractLanguageCode($pathinfo);

        return $info['remaining_pathinfo'];
    }

    /**
     * @return string
     */
    protected function getRelevantPathInfo()
    {
        $request_path_info = $this->request->getPathInfo();

        return $this->mode_factory->getInternalPath($request_path_info);
    }

    /**
     * @return bool
     */
    public function isSpecialPath()
    {
        if (RequestUtils::isLowRequest($this->request)) {
            return true;
        }

        if (IsProxyRequestHelper::check($this->request)) {
            return true;
        }

        $pathinfo = $this->request->getPathInfo();

        if ('/_' === substr($pathinfo, 0, 2) || preg_match('#^/portal/api(/|\?|$)#i', $pathinfo)) {
            return true;
        }

        // if we have a router, get the route name and compare it with the list of special routes
        if ($this->router) {
            $params = $this->router->match($this->getRoutablePath());
            if (isset($params['_route'])) {
                $route_name = $params['_route'];

                return in_array($route_name, self::$special_routes);
            }
        }

        return false;
    }
}
