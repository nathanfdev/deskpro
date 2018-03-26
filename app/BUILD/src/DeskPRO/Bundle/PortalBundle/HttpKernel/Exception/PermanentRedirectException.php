<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\HttpKernel\Exception;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Throw this to immediately (301) permanently redirect to the
 * route name and params you provide.
 */
class PermanentRedirectException extends \RuntimeException
{
    /**
     * @var string
     */
    private $route_name;

    /**
     * @var array
     */
    private $route_params;

    /**
     * @var string
     */
    private $url_type;

    public function __construct($route_name, array $route_params, $url_type = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $this->message      = 'Permenantly Redirecting';
        $this->code         = 301;
        $this->route_name   = $route_name;
        $this->route_params = $route_params;
        $this->url_type     = $url_type;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->route_name;
    }

    /**
     * @return array
     */
    public function getRouteParams()
    {
        return $this->route_params;
    }

    /**
     * @return string
     */
    public function getUrlType()
    {
        return $this->url_type;
    }
}
