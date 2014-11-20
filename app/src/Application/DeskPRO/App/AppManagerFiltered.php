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
     * $filter mus take an AppPackage and return truthy if it passes the filter or falsey if not.
     *
     * @param AppManagerInterface $app_manager
     * @param callable            $filter      The filter to filter app packages by
     */
    public function __construct(AppManagerInterface $app_manager, $filter)
    {
        $this->app_manager = $app_manager;
        $this->filter      = $filter;
    }

    /**
     * @param  string $name
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
     * @param  string                    $name
     * @return AppPackage
     * @throws \InvalidArgumentException
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
        $packages = array();

        foreach ($this->app_manager->getAllPackages() as $p) {
            if (call_user_func($this->filter, $p)) {
                $packages[] = $p;
            }
        }

        return $packages;
    }

    /**
     * @param  int  $id
     * @return bool
     */
    public function hasApp($id)
    {
        if (!$this->app_manager->hasApp($id)) {
            return false;
        }

        $app = $this->app_manager->getApp($id);

        return call_user_func($this->filter, $app->package) ? true : false;
    }

    /**
     * @param  int                       $id
     * @return AppInstance
     * @throws \InvalidArgumentException
     */
    public function getApp($id)
    {
        $app = $this->app_manager->getApp($id);
        if (!call_user_func($this->filter, $app->package)) {
            throw new \InvalidArgumentException();
        }

        return $app;
    }

    /**
     * @return AppInstance[]
     */
    public function getAllApps()
    {
        $apps = array();

        foreach ($this->app_manager->getAllApps() as $app) {
            if (call_user_func($this->filter, $app->package)) {
                $apps[] = $app;
            }
        }

        return $apps;
    }

    /**
     * @param  string        $name The package name
     * @return AppInstance[]
     */
    public function getPackageApps($name)
    {
        if (!$this->hasPackage($name)) {
            return array();
        }

        return $this->app_manager->getPackageApps($name);
    }
}
