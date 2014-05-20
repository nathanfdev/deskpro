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

use Application\DeskPRO\App\Native\NativeApp;
use Application\DeskPRO\App\Native\NativePackageConfig;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\AppPackage;

class AppManager implements AppManagerInterface
{
	/**
	 * @var AppPackage[]
	 */
	private $packages = array();

	/**
	 * Apps grouped by package name
	 * @var array
	 */
	private $package_to_apps = array();

	/**
	 * @var AppInstance[]
	 */
	private $apps = array();

	/**
	 * Cache of native configs per package
	 * @var array
	 */
	private $native_package_configs = array();

	/**
	 * @var \Application\DeskPRO\App\Native\NativeApp[]
	 */
	private $native_apps = array();

	/**
	 * @var AppServiceContainer
	 */
	private $app_service_container;

	/**
	 * Paths to apps on the filesystem
	 * @var array
	 */
	private $app_paths = array();


	/**
	 * @param AppPackage[] $packages
	 * @param array $app_paths
	 * @param AppInstance[] $apps
	 * @param AppServiceContainer $app_service_container
	 */
	public function __construct(array $packages, array $apps, array $app_paths, AppServiceContainer $app_service_container)
	{
		$this->app_service_container = $app_service_container;

		if (isset($app_paths['default']) && count($app_paths) > 1) {
			$default = $app_paths['default'];
			unset($app_paths['default']);
			$app_paths['default'] = $default;
		}

		$this->app_paths = $app_paths;

		foreach ($packages as $package) {
			$this->packages[$package->name] = $package;
		}
		foreach ($apps as $app) {
			$this->apps[$app->id] = $app;

			$pname = $app->package->name;
			if (!isset($this->package_to_apps[$pname])) {
				$this->package_to_apps[$pname] = array();
			}
			$this->package_to_apps[$pname][] = $app;

			if ($app->package->native_name) {
				$this->getNativeApp($app);
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
	 * @param string|AppPackage $name
	 * @return AppInstance[]
	 */
	public function getPackageApps($name)
	{
		if ($name instanceof AppPackage) {
			$name = $name->name;
		}

		if (!isset($this->package_to_apps[$name])) {
			return array();
		}

		return $this->package_to_apps[$name];
	}


	/**
	 * Gets a single app for a package.
	 *
	 * @param string|AppPackage $name
	 * @return AppInstance
	 */
	public function getPackageApp($name)
	{
		if ($name instanceof AppPackage) {
			$name = $name->name;
		}

		if (!isset($this->package_to_apps[$name])) {
			return null;
		}

		return $this->package_to_apps[$name][0];
	}


	/**
	 * Checks if a package has been installed at least once
	 *
	 * @param string|AppPackage $name
	 * @return bool
	 */
	public function isPackageInstalled($name)
	{
		if ($name instanceof AppPackage) {
			$name = $name->name;
		}

		return isset($this->package_to_apps[$name]);
	}


	/**
	 * Gets an AppManager with a specific scope filter applied to it
	 *
	 * @param string $scope The scope to search for
	 * @return AppManagerInterface
	 */
	public function getScopeFilter($scope)
	{
		$manager = new AppManagerFiltered($this, function(AppPackage $package) use ($scope) {
			return in_array($scope, $package->scopes);
		});

		return $manager;
	}


	/**
	 * @param AppInstance|int $app The app or app_id
	 * @return NativeApp
	 */
	public function getNativeApp($app)
	{
		if ($app instanceof AppInstance) {
			$app_id = $app->id;
		} else {
			$app = $this->getApp($app);
			$app_id = $app->id;
		}

		if (!$app->package->native_name) {
			throw new \InvalidArgumentException("{$app->package->name} is not a native app package");
		}

		if (isset($this->native_apps[$app_id])) {
			return $this->native_apps[$app_id];
		}

		$native_config = $this->getNativePackageConfig($app->package);
		$native_app = new NativeApp($app, $native_config);
		$this->native_apps[$app_id] = $native_app;

		$this->app_service_container->registerNativeApp($native_app);

		return $native_app;
	}


	/**
	 * @param AppPackage $package
	 * @return NativePackageConfig
	 * @throws \InvalidArgumentException
	 */
	public function getNativePackageConfig(AppPackage $package)
	{
		if (!$package->native_name) {
			throw new \InvalidArgumentException();
		}

		if (isset($this->native_package_configs[$package->name])) {
			return $this->native_package_configs[$package->name];
		} else {
			$native_config = NativePackageConfig::createFromPackage($package, $this->getAppPath($package->name));
			$this->native_package_configs[$package->name] = $native_config;
		}

		$this->_initNativePackageAutoload($native_config);

		return $native_config;
	}


	/**
	 * @param NativePackageConfig $native_config
	 */
	private function _initNativePackageAutoload(NativePackageConfig $native_config)
	{
		static $has_reg = array();

		$namespace = $native_config->getClassNamespace();
		$directory = $native_config->getNativeDir();

		if (isset($has_reg[$namespace])) {
			return;
		}

		$has_reg[$namespace] = true;

		spl_autoload_register(function($class_name) use ($namespace, $directory) {
			if (strpos($class_name, $namespace.'\\') !== 0) {
				return false;
			}

			$inc_name = str_replace($namespace.'\\', '', $class_name);
			$inc_name = str_replace('\\', DIRECTORY_SEPARATOR, $inc_name);
			$inc_name .= '.php';

			include($directory . DIRECTORY_SEPARATOR . $inc_name);

			return true;
		});
	}


	/**
	 * @param string $name
	 * @param AppInstance|NativeApp|int $app
	 * @return mixed
	 */
	public function getService($name, $app = null)
	{
		return $this->app_service_container->getService($name, $app);
	}


	/**
	 * Gets the base app path for a given app name.
	 *
	 * @param string $app_name
	 * @return string|null
	 */
	public function getAppPath($app_name)
	{
		foreach ($this->app_paths as $prefix => $path) {
			if ($prefix === 'default') {
				return $path . '/' . $app_name;
			} else if (strpos($app_name, $prefix) === 0) {
				return $path . '/' . $app_name;
			}
		}
		return null;
	}


	/**
	 * @return string[]
	 */
	public function getAppReposPaths()
	{
		return $this->app_paths;
	}
}