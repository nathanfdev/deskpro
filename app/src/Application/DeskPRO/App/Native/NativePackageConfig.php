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

namespace Application\DeskPRO\App\Native;

use Application\DeskPRO\Entity\AppPackage;
use Orb\Util\Arrays;

class NativePackageConfig
{
	/**
	 * @var string
	 */
	private $native_name;

	/**
	 * @var string
	 */
	private $class_namespace;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var string
	 */
	private $app_dir;

	/**
	 * @param AppPackage $package
	 * @param string $app_dir
	 * @return NativePackageConfig
	 */
	public static function createFromPackage(AppPackage $package, $app_dir)
	{
		$path = $app_dir . '/native/native_config.php';

		if (file_exists($path)) {
			$config = require($path);
		} else {
			$config = array();
		}

		return new self($package->name, $app_dir, $config);
	}


	/**
	 * @param string $native_name
	 * @param string $app_dir
	 * @param array $config
	 */
	public function __construct($native_name, $app_dir, array $config)
	{
		$this->native_name = $native_name;
		$this->app_dir = $app_dir;
		$this->config = $config;
	}


	/**
	 * @return string
	 */
	public function getNativeName()
	{
		return $this->native_name;
	}


	/**
	 * @return string|null
	 */
	public function getApiPackageRequestHandlerClass()
	{
		return isset($this->config['api']['package_request_handler']) ? $this->config['api']['package_request_handler'] : null;
	}


	/**
	 * @return string|null
	 */
	public function getApiAppRequestHandlerClass()
	{
		return isset($this->config['api']['app_request_handler']) ? $this->config['api']['app_request_handler'] : null;
	}


	/**
	 * @return string|null
	 */
	public function getAgentRequestHandlerClass()
	{
		return isset($this->config['agent']['request_handler']) ? $this->config['agent']['request_handler'] : null;
	}


	/**
	 * @return string|null
	 */
	public function getInstallerHandlerClass()
	{
		return isset($this->config['install']['handler']) ? $this->config['install']['handler'] : null;
	}


	/**
	 * @return string|null
	 */
	public function getEventHandlerClass()
	{
		return isset($this->config['event']['handler']) ? $this->config['event']['handler'] : null;
	}


	/**
	 * @return array
	 */
	public function getServices()
	{
		return isset($this->config['services']) ? $this->config['services'] : array();
	}


	/**
	 * @return string
	 */
	public function getClassNamespace()
	{
		if ($this->class_namespace === null) {
			$this->class_namespace = preg_replace('#[^a-zA-Z0-9]#', '_', $this->native_name);
		}

		return $this->class_namespace;
	}


	/**
	 * @return string
	 */
	public function getAppDir()
	{
		return $this->app_dir;
	}


	/**
	 * @return string
	 */
	public function getNativeDir()
	{
		return $this->app_dir . '/native';
	}
}