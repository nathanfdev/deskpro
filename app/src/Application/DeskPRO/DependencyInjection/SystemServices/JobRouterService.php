<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\JobQueue\JobRouter;
use Application\DeskPRO\JobQueue\Processor\IncomingSmsProcessor;
use Application\DeskPRO\JobQueue\Processor\OutgoingFacebookFeedProcessor;
use Application\DeskPRO\JobQueue\Processor\OutgoingSmsProcessor;
use Application\DeskPRO\Sms\Detector\PersonDetector;
use Application\DeskPRO\Sms\Detector\SmsAccountDetector;
use Application\DeskPRO\Sms\Detector\TicketDetector;

class JobRouterService
{
    public static function create(DeskproContainer $container)
    {
        $conn = $container->get('doctrine.dbal.default_connection');
        $em = $container->getEm();
        $queue = $container->getJobQueue();

        $router = new JobRouter($conn);

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

        return $router;
    }
}
