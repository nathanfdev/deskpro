<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper;

use Application\EmailBundle\SourceMapper\EmailRateLimit\EmailRateLimitFactory;
use Application\EmailBundle\SourceMapper\PendingQueuer\RedisPendingQueuer;
use Predis;
use Symfony\Component\DependencyInjection\Container;

class DeskproSourceMapperFactory
{
    public static function getSourceMapper(Container $container)
    {
        $source_mapper = new DatabaseSourceMapper(
            $container->get('database_connection'),
            $container->get('deskpro.blob_storage'),
            $container->get('email.email_account_manager'),
            $container->get('email.log_collector')
        );

        $rate_limit = EmailRateLimitFactory::create($container);
        $source_mapper->setRateLimit($rate_limit);

        $env = $container->get('deskpro.app_env');

        if ($info = $env->getConfig('settings.sendmail_redis_queue')) {
            // see https://github.com/nrk/predis/wiki/Connection-Parameters
            $client       = new Predis\Client($info);
            $redis_queuer = new RedisPendingQueuer($client, 'sendmail_queue');

            $external = new ExternalPendingQueue($source_mapper, $redis_queuer);

            return $external;
        } else {
            return $source_mapper;
        }
    }
}
