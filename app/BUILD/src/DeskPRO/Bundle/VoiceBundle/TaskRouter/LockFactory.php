<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;

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
        if (SemaphoreStore::isSupported()) {
            $store = new SemaphoreStore();
        } else {
            $store = new FlockStore($container->get('deskpro.app_env')->getUserTmpDir());
        }

        $factory = new Factory($store);

        return $factory->createLock('voice-task-router', 30);
    }
}
