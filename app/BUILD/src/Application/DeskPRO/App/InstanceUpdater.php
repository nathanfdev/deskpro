<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App;

use Application\DeskPRO\App\Native\InstallerHandler\AbstractUsersourceInstallerHandler;
use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\App\Native\InstallerHandler\NoopInstallerHandler;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\AppPackage;
use Doctrine\ORM\EntityManager;

class InstanceUpdater
{
    /**
     * @var AppManager
     */
    private $manager;

    /**
     * @var \Application\DeskPRO\Entity\AppInstance
     */
    private $app;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param AppManager    $manager
     * @param AppPackage    $package
     * @param EntityManager $em
     */
    public function __construct(AppManager $manager, AppInstance $app, EntityManager $em)
    {
        $this->manager = $manager;
        $this->app     = $app;
        $this->em      = $em;
    }

    /**
     * @param string           $title
     * @param array            $settings
     * @param DeskproContainer $container
     *
     * @return AppInstance
     */
    public function update($title, array $settings, DeskproContainer $container)
    {
        $settings = InstanceInstaller::readAppSettings($this->app->package, $settings);
        $context  = $this->createInstallContext($this->app->package, $this->app, $settings, $container);
        $handler  = $this->createInstallHandler();

        $settings = $handler->processSettings($context, $settings);
        $this->app->setSettings($settings ?: []);
        $this->app->title = $title ?: $this->app->package->title;

        $this->em->persist($this->app);
        $this->em->flush();

        $handler->updateSettings($context);

        // if this package is a usersource package, we should always check with the manager to avoid invalid SSO configurations
        if ($context->getNativeApp() && $context->getPackage()->isUsersource()) {
            $container->getSystemService('usersource_manager')->ensureSsoSettings($context->getUsersource());
        }
    }

    /**
     * @param AppPackage       $package
     * @param AppInstance      $app
     * @param array            $settings
     * @param DeskproContainer $container
     *
     * @throws \UnexpectedValueException
     *
     * @return InstallerContext
     */
    protected function createInstallContext(AppPackage $package, AppInstance $app, array $settings, DeskproContainer $container)
    {
        if ($package->native_name) {
            $native_app = $this->manager->getNativeApp($app);
            $usersource = null;

            if ($package->isUsersource()) {
                $q = $this->em->createQuery('
                SELECT us
                FROM DeskPRO:Usersource us
                WHERE us.app = :app
                ');
                $q->setParameter('app', $app);
                $usersource = $q->getOneOrNullResult();

                if (!$usersource) {
                    throw new \UnexpectedValueException('a usersource app instance MUST have a usersource pointing to it, app.id='.$app->id.' does not!');
                }
            }

            return new InstallerContext($container, $native_app, $settings, $usersource);
        }

        return new InstallerContext($container, null, $settings);
    }

    /**
     * Native apps have their own install handler (usually), but we always return the NoopInstallerHandler so we always have a handler.
     *
     * @return Native\InstallerHandler\InstallerHandlerInterface
     */
    protected function createInstallHandler()
    {
        if ($this->app->package->native_name) {
            $native_app = $this->manager->getNativeApp($this->app);
            if ($class = $native_app->getConfig()->getInstallerHandlerClass()) {
                return new $class($this->app->package['settings_def']);
            }
        }

        return new NoopInstallerHandler();
    }

    public function disableSso()
    {
        $handler = $this->createInstallHandler();

        if ($handler instanceof AbstractUsersourceInstallerHandler) {
            $handler->disableSsoSettings($this->app, $this->em);
        }
    }
}
