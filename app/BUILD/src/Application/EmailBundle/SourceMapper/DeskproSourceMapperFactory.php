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

/**
 * Source mapper factory reads settings from config.settings.php to determine how to process
 * outgoing email.
 *
 * === DEFAULT ===
 *
 * The default is to queue messages to the DB which get sent on cron.
 *
 * === CLOUD :: Redis Queue ===
 *
 * The configuration for the redis queue strategy of delivering outgoing email.
 *
 * Most likely you'll want to like the `key` parameter set to the default value below. If you change it, make sure the
 * same value is changed/configured for cloud email consumers.
 *
 * <code>
 * $SETTINGS['cloudemail_outgoing_redis_queue'] = [
 *     'connection' => [
 *         'scheme' => 'tcp',
 *         'host'   => '10.0.0.1',
 *         'port'   => 6379,
 *     ],
 *     'key' => 'outgoing-queue'
 * ];
 * </code>
 *
 * === CLOUD :: SQS Queue ===
 *
 * The url to an sqs queue which replaces the redis queue.
 *
 * <code>
 * $SETTINGS['cloudemail_outgoing_sqs_queue'] = "https://sqs.<region>.amazonaws.com/<aws_account_id>/outgoing.fifo";
 * </code>
 *
 * === Generic Redis Queue ===
 *
 * This also works on premise, though is undocumented and is only basic and works only with non-clustered server.
 * See https://github.com/nrk/predis/wiki/Connection-Parameters
 *
 * <code>
 * $SETTINGS['sendmail_redis_queue'] = [...config...];
 * </code>
 */
class DeskproSourceMapperFactory
{
    /**
     * @param Container $container
     *
     * @throws \Exception
     *
     * @return DatabaseSourceMapper|ExternalPendingQueue
     */
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

        if ($redis = $env->getConfig('settings.cloudemail_outgoing_redis_queue')) {
            $queuer   = CloudEmailPendingQueuer::create($redis['connection'], $redis['key']);
            $external = new ExternalPendingQueue($source_mapper, $queuer);

            return $external;
        } elseif ($queueUrl = $env->getConfig('settings.cloudemail_outgoing_sqs_queue')) {
            /** @var SettingsResolver $resolver */
            $resolver = $container->get('settings_resolver');
            $apiKey   = $resolver->getGlobalSettings()->get('api_auth.master_key', '');
            $queuer   = CloudEmailPendingSQSQueuer::create($queueUrl, $apiKey);

            $external = new ExternalPendingQueue($source_mapper, $queuer);

            return $external;
        } elseif ($info = $env->getConfig('settings.sendmail_redis_queue')) {
            $client       = new Predis\Client($info);
            $redis_queuer = new RedisPendingQueuer($client, 'sendmail_queue');

            $external = new ExternalPendingQueue($source_mapper, $redis_queuer);

            return $external;
        } else {
            return $source_mapper;
        }
    }
}
