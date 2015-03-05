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

use Application\DeskPRO\App\Native\NativeApp;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Orb\Util\Arrays;

class AppServiceContainer
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * Map of services: array('package' => array('service_name' => 'service\\class'))
     * A special key '@single' exists for services on is_single packages.
     *
     * @var array
     */
    private $package_service_names = array();

    /**
     * Map of service instances: array('app_id' => array('service_name' => instance))
     * A special key '@single' exists for services created on is_single apps.
     *
     * @var array
     */
    private $app_services = array();


    /**
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }


    /**
     * @param NativeApp $native_app
     */
    public function registerNativeApp(NativeApp $native_app)
    {
        $package = $native_app->getPackage();
        $app = $native_app->getApp();

        if (isset($this->package_service_names[$package->name])) {
            return;
        }

        $this->package_service_names[$package->name] = array();

        // Set up services
        $app_services = $native_app->getConfig()->getServices();
        if ($app_services) {

            $app_services = Arrays::keyFromData($app_services, 'id');
            $app_services = array_map(function ($x) use ($app) { $x['app'] = $app; return $x; }, $app_services);
            $this->package_service_names[$package->name] = $app_services;

            if ($package->is_single) {
                if (!isset($this->package_service_names['@single'])) {
                    $this->package_service_names['@single'] = $app_services;
                } else {
                    $this->package_service_names['@single'] = array_merge($this->package_service_names['@single'], $app_services);
                }
            }
        }
    }


    /**
     * @param  string                    $name The name of the service
     * @param  null                      $app  The app the service belongs to. If the app is a single-install app, this can be left out.
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getService($name, $app = null)
    {
        #------------------------------
        # Get service for an app
        #------------------------------

        if ($app !== null) {
            if ($app instanceof NativeApp) {
                $app = $app->getApp();
            }

            if (!isset($this->app_services[$app->id][$name])) {
                $package = $app->package;
                if (!isset($this->package_service_names[$package->name][$name])) {
                    throw new \InvalidArgumentException("Unknown service $name for app package {$package->name}");
                }

                $service_info = $this->package_service_names[$package->name][$name];
                $service_factory = $service_info['class'];
                $service = $service_factory::create($this->container, $app, isset($service_info['options']) ? $service_info['options'] : null);

                if (!isset($this->app_services[$app->id])) {
                    $this->app_services[$app->id] = array();
                }

                $this->app_services[$app->id][$name] = $service;

                if ($app->package->is_single) {
                    if (!isset($this->app_services['@single'])) {
                        $this->app_services['@single'] = array();
                    }
                    $this->app_services['@single'][$name] = $service;
                }
            }

            return $this->app_services[$app->id][$name];
        }

        #------------------------------
        # Get a service for a single person
        #------------------------------

        if (!isset($this->app_services['@single'][$name])) {
            if (!isset($this->package_service_names['@single'][$name])) {
                throw new \InvalidArgumentException("Unknown service $name for app package @single");
            }

            $service_info = $this->package_service_names['@single'][$name];
            $service_factory = $service_info['class'];
            $service = $service_factory::create($this->container, $service_info['app'], isset($service_info['options']) ? $service_info['options'] : null);

            if (!isset($this->app_services['@single'])) {
                $this->app_services['@single'] = array();
            }
            $this->app_services['@single'][$name] = $service;
        }

        return $this->app_services['@single'][$name];
    }
}
