<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
