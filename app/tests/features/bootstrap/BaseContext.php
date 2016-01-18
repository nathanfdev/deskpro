<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext as KernelAwareContextInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class BaseContext extends RawMinkContext implements KernelAwareContextInterface
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    public function resetAllContext()
    {
        // after any kind of re-install, we need to reboot the
        // kernel to reset references
        $this->kernel->shutdown();
        $this->kernel->boot();
    }

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        if ($this instanceof RebootableContextInterface) {
            $this->rebootContext();
        }
    }

    public function get($service_id)
    {
        return $this->getContainer()->get($service_id);
    }

    /**
     * Returns HttpKernel instance.
     *
     * @return KernelInterface
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Returns HttpKernel service container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    public function getEntityRepo($entity_name)
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository($entity_name);
    }

    public function persistAndFlush($entity)
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
