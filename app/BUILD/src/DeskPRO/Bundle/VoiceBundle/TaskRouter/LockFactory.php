<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter;

use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\SemaphoreStore;

/**
 * Class LockFactory.
 */
class LockFactory
{
    /**
     * @return \Symfony\Component\Lock\Lock
     */
    public static function createTaskRouterLock()
    {
        $store   = new SemaphoreStore();
        $factory = new Factory($store);

        return $factory->createLock('voice-task-router', 30);
    }
}
