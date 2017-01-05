<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DpBehat;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DpBehat\Data\ObjectsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class KernelAwareTrait.
 */
trait KernelAwareTrait
{
    /**
     * @var KernelInterface
     */
    private static $kernel;

    /**
     * @var ObjectsManager
     */
    private static $objectsManager;

    /**
     * @throws \Exception
     *
     * @return KernelInterface
     */
    protected static function getKernel()
    {
        if (!self::$kernel) {
            throw new \Exception('Kernel has not yet been initialized');
        }

        return self::$kernel;
    }

    /**
     * @return EntityManager
     */
    protected static function getEm()
    {
        return self::getKernel()->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Init ObjectManager.
     */
    protected static function initOm()
    {
        self::$objectsManager = self::createOm();
    }

    /**
     * @throws \Exception
     *
     * @return ObjectsManager
     */
    protected static function getOm()
    {
        if (!self::$objectsManager) {
            throw new \Exception('ObjectManager has not yet been initialized');
        }
        if (!self::$objectsManager->isClosed()) {
            throw new \Exception('ObjectManager is closed');
        }

        return self::$objectsManager;
    }

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel
     *
     * @throws \Exception
     */
    public function setKernel(KernelInterface $kernel)
    {
        self::$kernel = $kernel;

        if ($this instanceof RebootableContextInterface) {
            if (!method_exists($this, 'rebootContext')) {
                $class = get_class($this);
                throw new \Exception(
                     "$class needs to implement rebootContext() method to accept a RebootableContextInterface");
            }
            $this->rebootContext();
        }
    }

    /**
     * @throws \Exception
     *
     * @return KernelInterface
     */
    protected function kernel()
    {
        return self::getKernel();
    }

    /**
     * Returns HttpKernel service container.
     *
     * @return ContainerInterface
     */
    protected function container()
    {
        return $this->kernel()->getContainer();
    }

    /**
     * @param string $service
     *
     * @return object
     */
    protected function get($service)
    {
        return $this->container()->get($service);
    }

    /**
     * @return EntityManager
     */
    protected function em()
    {
        return self::getEm();
    }

    /**
     * @param string $entityName
     *
     * @return EntityRepository
     */
    protected function repository($entityName)
    {
        return $this->em()->getRepository($entityName);
    }

    /**
     * @param object $entity
     */
    protected function persistAndFlush($entity)
    {
        $this->em()->persist($entity);
        $this->em()->flush($entity);
    }

    /**
     * @throws \Exception
     *
     * @return ObjectsManager
     */
    protected function om()
    {
        return self::getOm();
    }

    /**
     * @throws \Exception
     *
     * @return ObjectsManager
     */
    private static function createOm()
    {
        return new ObjectsManager(self::getEm());
    }
}
