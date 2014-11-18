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

namespace Application\DeskPRO\App\Native;

use Application\DeskPRO\App\AppManager;
use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\App\Package\Package;
use Application\DeskPRO\App\Package\PackageInstaller;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\Monolog\NullLogger;
use Psr\Log\LoggerInterface;

class NativeAppsSync
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * @var \Application\DeskPRO\App\AppManager
     */
    private $manager;

    /**
     * @var \Application\DeskPRO\App\Package\PackageInstaller
     */
    private $package_installer;


    /**
     * @param DeskproContainer $container
     * @param AppManager       $manager
     * @param PackageInstaller $package_installer
     * @param LoggerInterface  $logger
     */
    public function __construct(DeskproContainer $container, AppManager $manager, PackageInstaller $package_installer, LoggerInterface $logger = null)
    {
        $this->container         = $container;
        $this->manager           = $manager;
        $this->package_installer = $package_installer;

        if (!$logger) {
            $logger = new NullLogger();
        }

        $this->logger = $logger;
    }


    /**
     * Updates apps already installed
     */
    public function runUpdates()
    {
        foreach ($this->manager->getAllPackages() as $package) {
            if ($package->native_name) {
                $this->_updateApp($package);
            }
        }
    }


    /**
     * @param  AppPackage                                $package
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Exception
     */
    private function _updateApp(AppPackage $package)
    {
        $this->logger->debug("Updating {$package->native_name}");

        // Updates the resources
        $app_package = new Package($this->manager->getAppPath($package->name, true));
        $this->package_installer->installPackage($app_package, $package);
        $this->logger->debug("... done install");

        // Updates any apps
        foreach ($this->manager->getPackageApps($package) as $app) {
            $native_app = $this->manager->getNativeApp($app);
            $class = $native_app->getConfig()->getInstallerHandlerClass();
            if ($class) {
                $this->logger->debug("... running update for app #{$app->id}");
                $context = new InstallerContext($this->container, $native_app);
                $obj = new $class($package['settings_def']);
                $obj->updatePackage($context);
                $this->logger->debug("... done");
            }
        }
    }


    /**
     * Syncs new apps from the filesystem
     */
    public function runSync()
    {
        foreach ($this->manager->getAppReposPaths() as $path) {
            $this->_syncAppsDir($path);
        }
    }


    /**
     * @param string $path
     */
    private function _syncAppsDir($path)
    {
        $dir = dir($path);
        while (($f = $dir->read()) !== false) {
            $f_path = $path.'/'.$f;
            if ($f == '.' || $f == '..' || !is_dir($f_path)) {
                continue;
            }

            $app_package = new Package($f_path);
            if ($this->manager->hasPackage($app_package->getManifest()->getPackageName())) {
                // already installed (will have been updated)
                continue;
            }

            $this->logger->debug("installing new native app {$f}");
            $this->package_installer->installPackage($app_package);
            $this->logger->debug("... done");
        }
    }
}
