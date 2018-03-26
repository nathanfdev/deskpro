<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Incoming\ProcQueue;

use Symfony\Component\DependencyInjection\Container;

class DeskproProcQueueFactory
{
    public static function create(Container $container)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        if (!$DP_ENV->getConfig('async_email_processing.process')) {
            return new NoopProcQueue();
        } else {
            $client = new \Predis\Client($DP_ENV->getConfig('async_email_processing.process.redis_params'));

            return new RedisProcQueue(
                $client,
                $DP_ENV->getConfig('async_email_processing.process.redis_key')
            );
        }
    }
}
