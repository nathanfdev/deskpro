<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter;

use DeskPRO\Component\Lock\PdoStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\RetryTillSaveStore;

/**
 * Class LockFactory.
 */
class LockFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return \Symfony\Component\Lock\Lock
     */
    public static function createTaskRouterEvaluateLock(ContainerInterface $container)
    {
        $store   = new RetryTillSaveStore(new PdoStore($container->get('doctrine.dbal.default_connection')), 750, 2);
        $factory = new Factory($store);

        return $factory->createLock('voice-task-router', 20);
    }

    /**
     * @param ContainerInterface $container
     *
     * @return \Symfony\Component\Lock\Lock
     */
    public static function createTaskRouterActionsLock(ContainerInterface $container)
    {
        $store   = new RetryTillSaveStore(new PdoStore($container->get('doctrine.dbal.default_connection')), 500);
        $factory = new Factory($store);

        return $factory->createLock('voice-task-router', 20);
    }
}
