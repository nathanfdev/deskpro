<?php

/**
 * DeskPRO.
 *
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
     * @var callable
     */
    private $exception_handler = false;

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
     * Sets an exception handler to run on problems during sync or upgrades.
     */
    public function setExceptionHandler($exception_handler)
    {
        $this->exception_handler = $exception_handler;
    }

    public function deleteUnexisting()
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        foreach ($this->manager->getAllPackages() as $package) {
            if ($package->native_name && strpos($package->name, 'deskpro_') === 0 && !$this->manager->getAppPath($package->name, true)) {
                $this->manager->removePackage($package);
                $em->remove($package);
                $this->logger->debug(sprintf('Removing application %s.', $package->name));
            }
        }
        $em->flush();
    }

    /**
     * Updates apps already installed.
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
     * @param AppPackage $package
     *
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
        $app_package = null;
        try {
            $app_package = new Package($this->manager->getAppPath($package->name, true));
            $this->package_installer->installPackage($app_package, $package);
        } catch (\Exception $e) {
            $this->logger->error("EXCEPTION: {$e->getMessage()}");
            if ($this->exception_handler) {
                call_user_func($this->exception_handler, $e, ['mode' => 'install', 'package' => $app_package, 'manager' => $this->manager]);
            } else {
                throw $e;
            }
        }
        $this->logger->debug('... done install');

        // Updates any apps
        foreach ($this->manager->getPackageApps($package) as $app) {
            $native_app = $this->manager->getNativeApp($app);

            $class = $native_app->getConfig()->getInstallerHandlerClass();
            if ($class) {
                $this->logger->debug("... running update for app #{$app->id}");
                $context = new InstallerContext($this->container, $native_app);
                $obj     = new $class($package['settings_def']);

                try {
                    $obj->updatePackage($context);
                } catch (\Exception $e) {
                    $this->logger->error("EXCEPTION: {$e->getMessage()}");
                    if ($this->exception_handler) {
                        call_user_func($this->exception_handler, $e, ['mode' => 'update', 'package' => $app_package, 'app' => $app, 'manager' => $this->manager]);
                    } else {
                        throw $e;
                    }
                }
                $this->logger->debug('... done');
            }
        }
    }

    /**
     * Syncs new apps from the filesystem.
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
        $this->logger->debug("syncing apps dir: $path");

        if (!is_dir($path)) {
            $this->logger->debug('(no dir)');

            return;
        }

        $dir = dir($path);
        while (($f = $dir->read()) !== false) {
            $f_path = $path.'/'.$f;
            if ($f == '.' || $f == '..' || !is_dir($f_path)) {
                continue;
            }

            try {
                $app_package = new Package($f_path);
            } catch (\Exception $e) {
                $this->logger->error("EXCEPTION: {$e->getMessage()}");
                if ($this->exception_handler) {
                    call_user_func($this->exception_handler, $e, ['mode' => 'install', 'manager' => $this->manager]);
                    continue;
                } else {
                    throw $e;
                }
            }

            if ($this->manager->hasPackage($app_package->getManifest()->getPackageName())) {
                // already installed (will have been updated)
                continue;
            }

            if ($app_package->getManifest()->getPackageName() !== $f) {
                // business logic does not allow manifest package name and name of directory
                // that contains it to be different see AppManager::getAppPath
                $this->logger->error(sprintf(
                    'Package name in manifest [%s] does not match directory name [%s], skipping',
                    $app_package->getManifest()->getPackageName(),
                    $f
                ));
                continue;
            }

            /* @var \DpRun\DpEnv $DP_ENV */
            global $DP_ENV;

            if (in_array(AppPackage::TAG_CLOUD_ONLY, $app_package->getManifest()->getTags()) && !(defined('DPC_IS_CLOUD') || $DP_ENV->getConfig('env.server_id') === 'builder.deskprodemo.com')) {
                $this->logger->debug("cloud only app -- skipping {$f}");
                continue;
            }

            $this->logger->debug("installing new native app {$f}");
            try {
                $this->package_installer->installPackage($app_package);
            } catch (\Exception $e) {
                $this->logger->error("EXCEPTION: {$e->getMessage()}");
                if ($this->exception_handler) {
                    call_user_func($this->exception_handler, $e, ['mode' => 'install', 'package' => $app_package, 'manager' => $this->manager]);
                    continue;
                } else {
                    throw $e;
                }
            }
            $this->logger->debug('... done');
        }
    }
}
