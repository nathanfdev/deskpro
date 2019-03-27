<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper;

use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\EmailBundle\SourceMapper\EmailRateLimit\EmailRateLimitFactory;
use Application\EmailBundle\SourceMapper\PendingQueuer\CloudEmailPendingQueuer;
use Application\EmailBundle\SourceMapper\PendingQueuer\CloudEmailPendingSQSQueuer;
use Application\EmailBundle\SourceMapper\PendingQueuer\RedisPendingQueuer;
use Predis;
use Symfony\Component\DependencyInjection\Container;

class DeskproSourceMapperFactory
{
    /**
     * @param Container $container
     * @return DatabaseSourceMapper|ExternalPendingQueue
     * @throws \Exception
     */
    public static function getSourceMapper( Container $container)
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

        //TODO shouldn't we make sure only one setting is enabled at the same time
        if ($redis = $env->getConfig('settings.cloudemail_outgoing_redis_queue')) {
            $queuer = CloudEmailPendingQueuer::create($redis['connection'], $redis['set']);
            $external = new ExternalPendingQueue($source_mapper, $queuer);
            return $external;
        }
        elseif ($queueUrl = $env->getConfig('settings.cloudemail_outgoing_sqs_queue')) {
            /** @var SettingsResolver $resolver */
            $resolver = $container->get("settings_resolver");
            $apiKey = $resolver->getGlobalSettings()->get('api_auth.master_key', "");
            $queuer = CloudEmailPendingSQSQueuer::create($queueUrl, $apiKey);

            $external = new ExternalPendingQueue($source_mapper, $queuer);
            return $external;
        }
        else if ($info = $env->getConfig('settings.sendmail_redis_queue')) {
            // see https://github.com/nrk/predis/wiki/Connection-Parameters
            $client       = new Predis\Client($info);
            $redis_queuer = new RedisPendingQueuer($client, 'sendmail_queue');

            $external = new ExternalPendingQueue($source_mapper, $redis_queuer);

            return $external;
        }

        else {
            return $source_mapper;
        }
    }
}
