<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App;

use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\App\Native\InstallerHandler\NoopInstallerHandler;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\Entity\Usersource;
use Doctrine\ORM\EntityManager;

class InstanceUninstaller
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
     * @param DeskproContainer $container
     */
    public function uninstall(DeskproContainer $container)
    {
        $handler = $this->createInstallHandler();
        $context = $this->createInstallContext($this->app->package, $this->app, [], $container);

        $handler->uninstall($context);

        $this->em->remove($this->app);
        $this->em->flush();
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
}
