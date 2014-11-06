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
 * @subpackage
 */

namespace Application\LanguageBundle\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher as BaseUrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;

class UrlMatcher implements UrlMatcherInterface, RequestMatcherInterface
{
	/**
	 * @var \Symfony\Component\Routing\Matcher\UrlMatcher
	 */
	private $symfony_matcher;

	private $context;


	public function __construct(BaseUrlMatcher $symfony_matcher)
	{
		$this->symfony_matcher = $symfony_matcher;
	}

	/**
	 * Sets the request context.
	 *
	 * @param RequestContext $context The context
	 * @api
	 */
	public function setContext(RequestContext $context)
	{
		$this->context = $context;
		$this->symfony_matcher->setContext($context);
	}


	/**
	 * Gets the request context.
	 *
	 * @return RequestContext The context
	 * @api
	 */
	public function getContext()
	{
		return $this->context = $this->symfony_matcher->getContext();
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
		return $this->symfony_matcher->match($pathinfo);
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
		return $this->symfony_matcher->matchRequest($request);
	}
}
 