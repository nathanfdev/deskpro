<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App;

use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\AppPackage;

class AppManagerFiltered implements AppManagerInterface
{
    /**
     * @var AppManagerInterface
     */
    private $app_manager;

    /**
     * @var callable
     */
    private $filter;

    /**
     * @var callable
     */
    private $app_filter;

    /**
     * $filter mus take an AppPackage and return truthy if it passes the filter or falsey if not.
     *
     * @param AppManagerInterface $app_manager
     * @param callable            $filter      The filter to filter app packages by
     * @param callable            $app_filter  The filter to filter apps by
     */
    public function __construct(AppManagerInterface $app_manager, $filter, $app_filter = null)
    {
        $this->app_manager = $app_manager;
        $this->filter      = $filter;
        $this->app_filter  = $app_filter;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasPackage($name)
    {
        if (!$this->app_manager->hasPackage($name)) {
            return false;
        }

        $package = $this->app_manager->getPackage($name);

        return call_user_func($this->filter, $package) ? true : false;
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
        $package = $this->app_manager->getPackage($name);
        if (!call_user_func($this->filter, $package)) {
            throw new \InvalidArgumentException();
        }

        return $package;
    }

    /**
     * @return AppPackage[]
     */
    public function getAllPackages()
    {
        $packages = [];

        foreach ($this->app_manager->getAllPackages() as $p) {
            if (call_user_func($this->filter, $p)) {
                $packages[] = $p;
            }
        }

        return $packages;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasApp($id)
    {
        if (!$this->app_manager->hasApp($id)) {
            return false;
        }

        $app = $this->app_manager->getApp($id);

        if (!call_user_func($this->filter, $app->package)) {
            return false;
        }

        if ($this->app_filter && !call_user_func($this->app_filter, $app)) {
            return false;
        }

        return true;
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
        $app = $this->app_manager->getApp($id);
        if (!call_user_func($this->filter, $app->package)) {
            throw new \InvalidArgumentException();
        }

        if ($this->app_filter && !call_user_func($this->app_filter, $app)) {
            throw new \InvalidArgumentException();
        }

        return $app;
    }

    /**
     * @return AppInstance[]
     */
    public function getAllApps()
    {
        $apps = [];

        foreach ($this->app_manager->getAllApps() as $app) {
            if (call_user_func($this->filter, $app->package)) {
                if ($this->app_filter && !call_user_func($this->app_filter, $app)) {
                    continue;
                }
                $apps[] = $app;
            }
        }

        return $apps;
    }

    /**
     * @param string $name The package name
     *
     * @return AppInstance[]
     */
    public function getPackageApps($name)
    {
        if (!$this->hasPackage($name)) {
            return [];
        }

        $all_apps = $this->app_manager->getPackageApps($name);
        $apps     = [];

        foreach ($all_apps as $app) {
            if (call_user_func($this->filter, $app->package)) {
                if ($this->app_filter && !call_user_func($this->app_filter, $app)) {
                    continue;
                }
                $apps[] = $app;
            }
        }

        return $apps;
    }
}
