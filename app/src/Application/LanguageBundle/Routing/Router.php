<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Portal
 */

namespace Application\LanguageBundle\Routing;


use Application\LanguageBundle\Language\LanguageManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class Router implements WarmableInterface, RouterInterface, RequestMatcherInterface
{
	/**
	 * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
	 */
	private $router;

	/**
	 * @var \Application\LanguageBundle\Language\LanguageManager
	 */
	private $language_manager;


	public function __construct(BaseRouter $router, LanguageManager $language_manager)
	{
		$this->router = $router;
		$this->language_manager = $language_manager;
	}

	/**
	 * Sets the request context.
	 *
	 * @param RequestContext $context The context
	 * @api
	 */
	public function setContext(RequestContext $context)
	{
		$this->router->setContext($context);
	}


	/**
	 * Gets the request context.
	 *
	 * @return RequestContext The context
	 * @api
	 */
	public function getContext()
	{
		return $this->router->getContext();
	}


	/**
	 * Tries to match a request with a set of routes.
	 * If the matcher can not find information, it must throw one of the exceptions documented
	 * below.
	 *
	 * @param Request $request The request to match
	 * @return array An array of parameters
	 * @throws ResourceNotFoundException If no matching resource could be found
	 * @throws MethodNotAllowedException If a matching resource was found but the request method is not allowed
	 */
	public function matchRequest(Request $request)
	{
		return $this->router->matchRequest($request);
	}


	/**
	 * Gets the RouteCollection instance associated with this Router.
	 *
	 * @return RouteCollection A RouteCollection instance
	 */
	public function getRouteCollection()
	{
		return $this->router->getRouteCollection();
	}


	/**
	 * Generates a URL or path for a specific route based on the given parameters.
	 * Parameters that reference placeholders in the route pattern will substitute them in the
	 * path or host. Extra params are added as query string to the URL.
	 * When the passed reference type cannot be generated for the route because it requires a different
	 * host or scheme than the current one, the method will return a more comprehensive reference
	 * that includes the required params. For example, when you call this method with $referenceType = ABSOLUTE_PATH
	 * but the route requires the https scheme whereas the current scheme is http, it will instead return an
	 * ABSOLUTE_URL with the https scheme and the current host. This makes sure the generated URL matches
	 * the route in any case.
	 * If there is no route with the given name, the generator must throw the RouteNotFoundException.
	 *
	 * @param string      $name                    The name of the route
	 * @param mixed       $parameters              An array of parameters
	 * @param bool|string $referenceType           The type of reference to be generated (one of the constants)
	 * @return string The generated URL
	 * @throws RouteNotFoundException              If the named route doesn't exist
	 * @throws MissingMandatoryParametersException When some parameters are missing that are mandatory for the route
	 * @throws InvalidParameterException           When a parameter value for a placeholder is not correct because
	 *                                             it does not match the requirement
	 * @api
	 */
	public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
	{
		return $this->router->generate($name, $parameters, $referenceType);
	}


	/**
	 * Tries to match a URL path with a set of routes.
	 * If the matcher can not find information, it must throw one of the exceptions documented
	 * below.
	 *
	 * @param string $pathinfo The path info to be parsed (raw format, i.e. not urldecoded)
	 * @return array An array of parameters
	 * @throws ResourceNotFoundException If the resource could not be found
	 * @throws MethodNotAllowedException If the resource was found but the request method is not allowed
	 * @api
	 */
	public function match($pathinfo)
	{
		return $this->router->match($pathinfo);
	}


	/**
	 * Warms up the cache.
	 *
	 * @param string $cacheDir The cache directory
	 */
	public function warmUp($cacheDir)
	{
		$this->router->warmUp($cacheDir);
	}
}
 