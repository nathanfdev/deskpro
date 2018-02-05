<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\JobQueue\JobRouter;
use Application\DeskPRO\JobQueue\Processor\FeatureProcessor;
use Application\DeskPRO\JobQueue\Processor\IncomingSmsProcessor;
use Application\DeskPRO\JobQueue\Processor\OutgoingFacebookFeedProcessor;
use Application\DeskPRO\JobQueue\Processor\OutgoingSmsProcessor;
use Application\DeskPRO\JobQueue\Processor\Reset\UsersImportProcessor;
use Application\DeskPRO\JobQueue\Processor\UsersourceSyncProcessor;
use Application\DeskPRO\JobQueue\Processor\VoiceDownloadRecordProcessor;
use Application\DeskPRO\Sms\Detector\PersonDetector;
use Application\DeskPRO\Sms\Detector\SmsAccountDetector;
use Application\DeskPRO\Sms\Detector\TicketDetector;
use Application\LegacyApiBundle\Controller\ResetHelpdeskController;

class JobRouterService
{
    public static function create(DeskproContainer $container)
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn  = $container->get('doctrine.dbal.default_connection');
        $em    = $container->getEm();
        $queue = $container->getJobQueue();

        $router = new JobRouter($conn);

        /*************************************
         * usersource_sync
         */
        $router->addProcessor(
            new UsersourceSyncProcessor(
                $conn,
                $queue,
                $container->getSystemService('usersource_manager'),
                $container->getSystemService('usersource_sync_manager'),
                $container->get('dp_sys.alerts.event_logger')
            )
        );

        /*************************************
         * outgoing_sms
         */
        $router->addProcessor(
            new OutgoingSmsProcessor(
                $conn,
                $queue
            )
        );

        /*************************************
         * incoming_sms
         */
        $router->addProcessor(
            new IncomingSmsProcessor(
                $conn,
                new SmsAccountDetector($em),
                new PersonDetector($em),
                new TicketDetector($em),
                $container->getSystemService('ticket_manager')
            )
        );

        /*************************************
         * outgoing_facebook_feed
         */
        $router->addProcessor(
            new OutgoingFacebookFeedProcessor(
                $conn,
                $queue
            )
        );

        /*
         * todo instantiate processors on demand
         */
        foreach (ResetHelpdeskController::$types as $type) {
            $proc = 'Application\DeskPRO\JobQueue\Processor\Reset\\'.ucfirst($type).'Processor';
            if (class_exists($proc)) {
                $router->addProcessor(new $proc($container));
            }
        }

        $router->addProcessor(new UsersImportProcessor($container));

        // voice records processor
        $router->addProcessor(
            new VoiceDownloadRecordProcessor(
                $conn,
                $container->getEm(),
                $container->getBlobStorage(),
                $container->get('serializer'),
                $container->get('event_dispatcher')
            )
        );

        // features processor
        $router->addProcessor(
            new FeatureProcessor(
                $conn,
                $container->get('deskpro.toggle_feature_manager')
            )
        );

        return $router;
    }
}
