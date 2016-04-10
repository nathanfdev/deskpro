<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
    protected $kernel;

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel
     *
     * @throws \Exception
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
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
     * @param string $service
     *
     * @return object
     */
    protected function get($service)
    {
        return $this->getContainer()->get($service);
    }

    /**
     * Returns HttpKernel instance.
     *
     * @return KernelInterface
     */
    protected function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Returns HttpKernel service container.
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * @param string $entityName
     *
     * @return EntityRepository
     */
    protected function getEntityRepo($entityName)
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository($entityName);
    }

    /**
     * @param object $entity
     */
    protected function persistAndFlush($entity)
    {
        $this->getContainer()->get('doctrine.orm.default_entity_manager')->persist($entity);
        $this->getContainer()->get('doctrine.orm.default_entity_manager')->flush($entity);
    }

    /**
     * @return EntityManager
     */
    protected function em()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @param string $class
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository($class)
    {
        return $this->em()->getRepository($class);
    }
}
