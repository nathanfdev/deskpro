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

namespace Application\PortalBundle\HttpKernel\Exception;

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
		$this->message = 'Permenantly Redirecting';
		$this->code = 301;
		$this->route_name = $route_name;
		$this->route_params = $route_params;
		$this->url_type = $url_type;
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
 