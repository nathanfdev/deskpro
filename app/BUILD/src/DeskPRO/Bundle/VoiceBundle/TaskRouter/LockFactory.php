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
    public static function createTaskRouterLock(ContainerInterface $container)
    {
        $store   = new RetryTillSaveStore(new PdoStore($container->get('doctrine.dbal.default_connection')));
        $factory = new Factory($store);

        return $factory->createLock('voice-task-router', 30);
    }
}
