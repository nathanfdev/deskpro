<?php

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class AbstractDefaultData
{
    /**
     * Gets the priority. Lower numbers run first.
     * This typically only makes sense for runInstall.
     */
    const PRIORITY = 500;

    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param DeskproContainer $container
     * @param LoggerInterface  $logger
     */
    public function __construct(DeskproContainer $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger    = $logger;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEm()
    {
        return $this->container->getEm();
    }

    /**
     * @return \Application\DeskPRO\DBAL\Connection
     */
    protected function getDb()
    {
        return $this->container->getDb();
    }

    /**
     * @return DeskproContainer
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Called during a fresh install.
     */
    public function runInstall()
    {
    }

    /**
     * Called automatically during an upgrade when the system doesnt have the class installed.
     * Usually the same as runInstall.
     */
    public function runInstallViaUpgrade()
    {
        $this->runInstall();
    }

    /**
     * Called automatically during upgrades when the package has been installed before.
     * This is used to sync the database with any changes (e.g. adding new records or updating them).
     */
    public function runSync()
    {
    }

    /**
     * Called with the dp:reset-default-data command specifically. Usually the same as runSync.
     */
    public function runReset()
    {
        $this->runSync();
    }
}
