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
 * @category Entities
 */

namespace Application\DeskPRO\App;

use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\Entity\AppInstance;

class AppManager
{
	/**
	 * @var AppPackage[]
	 */
	private $packages = array();

	/**
	 * @var AppInstance[]
	 */
	private $apps = array();


	/**
	 * @param AppInstance[] $apps
	 */
	public function __construct(array $apps)
	{
		foreach ($apps as $app) {
			$this->apps[$app->id] = $app;

			$package = $app->package;
			if (!isset($this->packages[$package->name])) {
				$this->packages[$package->name] = $package;
			}
		}
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPackage($name)
	{
		return isset($this->packages[$name]);
	}


	/**
	 * @param string $name
	 * @return AppPackage
	 * @throws \InvalidArgumentException
	 */
	public function getPackage($name)
	{
		if (!isset($this->packages[$name])) {
			throw new \InvalidArgumentException();
		}

		return $this->packages[$name];
	}


	/**
	 * @return AppPackage[]
	 */
	public function getAllPackages()
	{
		return array_values($this->packages);
	}


	/**
	 * @param int $id
	 * @return bool
	 */
	public function hasApp($id)
	{
		return isset($this->apps[$id]);
	}


	/**
	 * @param int $id
	 * @return AppInstance
	 * @throws \InvalidArgumentException
	 */
	public function getApp($id)
	{
		if (!isset($this->apps[$id])) {
			throw new \InvalidArgumentException();
		}

		return $this->apps[$id];
	}


	/**
	 * @return AppInstance[]
	 */
	public function getAllApps()
	{
		return array_values($this->apps);
	}


	/**
	 * @param string $name  The package name
	 * @return AppInstance[]
	 */
	public function getPackageApps($name)
	{
		$apps = array();
		foreach ($this->apps as $app) {
			if ($app->package->name == $name) {
				$apps[] = $apps;
			}
		}

		return $apps;
	}
}