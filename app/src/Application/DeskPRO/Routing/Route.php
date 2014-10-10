<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 */

namespace Application\DeskPRO\Routing;

class Route extends \Symfony\Component\Routing\Route
{
	/**
	 * @param $path
	 * @param array $info
	 * @return Route
	 */
	public static function create(array $info)
	{
		$route = new self($info['path']);

		if ($route) {
			$route->setFromArray($info);
		}

		return $route;
	}

	/**
	 * @param array $info
	 */
	public function setFromArray(array $info)
	{
		if (isset($info['defaults']) && $info['defaults']) {
			if (isset($info['controller'])) {
				$info['defaults']['_controller'] = $info['controller'];
			}
			$this->setDefaults($info['defaults']);
		} elseif (isset($info['controller'])) {
			$this->setDefaults(array('_controller' => $info['controller']));
		}

		if (isset($info['requirements']) && $info['requirements']) {
			$this->setRequirements($info['requirements']);
		}
		if (isset($info['options']) && $info['options']) {
			$this->setOptions($info['options']);
		}
		if (isset($info['host']) && $info['host']) {
			$this->setHost($info['host']);
		}
		if (isset($info['schemes']) && $info['schemes']) {
			$this->setSchemes($info['schemes']);
		}
		if (isset($info['methods']) && $info['methods']) {
			$this->setMethods($info['methods']);
		}
	}
}