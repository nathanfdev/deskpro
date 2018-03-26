<?php

/**
 * DeskPRO.
 *
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
    private $packages = [];

    /**
     * Apps grouped by package name.
     *
     * @var array
     */
    private $package_to_apps = [];

    /**
     * @var AppInstance[]
     */
    private $apps = [];

    /**
     * Cache of native configs per package.
     *
     * @var array
     */
    private $native_package_configs = [];

    /**
     * @var \Application\DeskPRO\App\Native\NativeApp[]
     */
    private $native_apps = [];

    /**
     * @var AppServiceContainer
     */
    private $app_service_container;

    /**
     * Paths to apps on the filesystem.
     *
     * @var array
     */
    private $app_paths = [];

    /**
     * @var \Application\DeskPRO\Entity\Usersource[]
     */
    private $usersources;

    /**
     * @param AppPackage[]        $packages
     * @param array               $app_paths
     * @param AppInstance[]       $apps
     * @param AppServiceContainer $app_service_container
     */
    public function __construct(array $packages, array $apps, array $app_paths, AppServiceContainer $app_service_container, array $usersources = [])
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
                $this->package_to_apps[$pname] = [];
            }
            $this->package_to_apps[$pname][] = $app;

            if ($app->package->native_name) {
                $this->getNativeApp($app);
            }
        }

        $this->usersources = $usersources;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasPackage($name)
    {
        return isset($this->packages[$name]);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return AppPackage
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
     *
     * @return bool
     */
    public function hasApp($id)
    {
        return isset($this->apps[$id]);
    }

    /**
     * @param int $id
     *
     * @throws \InvalidArgumentException
     *
     * @return AppInstance
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
     *
     * @return AppInstance[]
     */
    public function getPackageApps($name)
    {
        if ($name instanceof AppPackage) {
            $name = $name->name;
        }

        if (!isset($this->package_to_apps[$name])) {
            return [];
        }

        return $this->package_to_apps[$name];
    }

    /**
     * Gets a single app for a package.
     *
     * @param string|AppPackage $name
     *
     * @return AppInstance
     */
    public function getPackageApp($name)
    {
        if ($name instanceof AppPackage) {
            $name = $name->name;
        }

        if (!isset($this->package_to_apps[$name])) {
            return;
        }

        return $this->package_to_apps[$name][0];
    }

    /**
     * Checks if a package has been installed at least once.
     *
     * @param string|AppPackage $name
     *
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
     * Gets an AppManager with a specific scope filter applied to it.
     *
     * @param string   $scope          The scope to search for
     * @param callable $package_filter Optionally specify a custom package filter
     * @param callable $app_filter     Optionally specify a custom app filter
     *
     * @return AppManagerInterface
     */
    public function getScopeFilter($scope, $package_filter = null, $app_filter = null)
    {
        $manager = new AppManagerFiltered($this, function (AppPackage $package) use ($scope, $package_filter) {
            if (!in_array($scope, $package->scopes)) {
                return false;
            }
            if ($package_filter && !call_user_func($package_filter, $package)) {
                return false;
            }

            return true;
        }, $app_filter);

        return $manager;
    }

    /**
     * @param AppInstance|int $app The app or app_id
     *
     * @return NativeApp
     */
    public function getNativeApp($app)
    {
        if ($app instanceof AppInstance) {
            $app_id = $app->id;
        } else {
            $app    = $this->getApp($app);
            $app_id = $app->id;
        }

        if (!$app->package->native_name) {
            throw new \InvalidArgumentException("{$app->package->name} is not a native app package");
        }

        if (isset($this->native_apps[$app_id])) {
            return $this->native_apps[$app_id];
        }

        $native_config              = $this->getNativePackageConfig($app->package);
        $native_app                 = new NativeApp($app, $native_config);
        $this->native_apps[$app_id] = $native_app;

        $this->app_service_container->registerNativeApp($native_app);

        return $native_app;
    }

    /**
     * @param AppPackage $package
     *
     * @throws \InvalidArgumentException
     *
     * @return NativePackageConfig
     */
    public function getNativePackageConfig(AppPackage $package)
    {
        if (!$package->native_name) {
            throw new \InvalidArgumentException();
        }

        if (isset($this->native_package_configs[$package->name])) {
            return $this->native_package_configs[$package->name];
        } else {
            $native_config                                = NativePackageConfig::createFromPackage($package, $this->getAppPath($package->name, true));
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
        static $has_reg = [];

        $namespace = $native_config->getClassNamespace();
        $directory = $native_config->getNativeDir();

        if (isset($has_reg[$namespace])) {
            return;
        }

        $has_reg[$namespace] = true;

        spl_autoload_register(function ($class_name) use ($namespace, $directory) {
            if (strpos($class_name, $namespace.'\\') !== 0) {
                return false;
            }

            $inc_name = str_replace($namespace.'\\', '', $class_name);
            $inc_name = str_replace('\\', DIRECTORY_SEPARATOR, $inc_name);
            $inc_name .= '.php';

            include $directory.DIRECTORY_SEPARATOR.$inc_name;

            return true;
        });
    }

    /**
     * @param string                    $name
     * @param AppInstance|NativeApp|int $app
     *
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
     * @param bool   $check_exists
     *
     * @return string|null
     */
    public function getAppPath($app_name, $check_exists = false)
    {
        if ($check_exists) {
            foreach ($this->app_paths as $path) {
                $p = $path.'/'.$app_name;
                if (file_exists($p)) {
                    return $p;
                }
            }
        } else {
            foreach ($this->app_paths as $prefix => $path) {
                if ($prefix === 'default') {
                    return $path.'/'.$app_name;
                } elseif (strpos($app_name, $prefix) === 0) {
                    return $path.'/'.$app_name;
                }
            }
        }

        return;
    }

    /**
     * @param AppInstance $appId
     *
     * @return \Application\DeskPRO\Entity\Usersource
     */
    public function getUsersourceForApp($appId, $type)
    {
        foreach ($this->usersources as $usersource) {
            if ($usersource->app && $usersource->app->id === $appId && $usersource->type == $type) {
                return $usersource;
            }
        }
    }

    /**
     * @return string[]
     */
    public function getAppReposPaths()
    {
        return $this->app_paths;
    }

    public function removePackage(AppPackage $package)
    {
        $name = $package->name;
        if (isset($this->packages[$name])) {
            unset($this->packages[$package->name]);
        }

        if (isset($this->package_to_apps[$name])) {
            /** @var AppInstance[] $apps */
            $apps = $this->package_to_apps[$name];
            foreach ($apps as $app) {
                if (isset($this->apps[$app->getId()])) {
                    unset($this->apps[$app->getId()]);
                }
            }
            unset($this->package_to_apps[$package->name]);
        }
    }
}
