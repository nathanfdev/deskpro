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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\Tickets\Actions\ActionApplicator;
use Application\DeskPRO\Tickets\Slas\SlaClientMessageSender;
use Application\DeskPRO\Tickets\Slas\SlaProcessor;

/**
 * Handles SLA warn/fail updates
 */
class TicketSlas extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    public function run()
    {
        $GLOBALS['DP_ESCALATION_RUNNING'] = true;

        $proc = new SlaProcessor(
            App::$container->getEm(),
            new ActionApplicator(App::$container),
            new SlaClientMessageSender(App::$container->getDb())
        );

        $context_factory = function () {
            $context = App::$container->getTicketManager()->createSystemExecutorContext('slas');

            return $context;
        };

        $count_failed = $proc->processAllFailed($context_factory, App::$container->getTicketManager());
        $count_warning = $proc->processAllWarning($context_factory, App::$container->getTicketManager());

        if ($count_warning || $count_failed) {
            $this->getLogger()->logInfo("SLA statuses updated. Failed: $count_failed, warning: $count_warning");
        }

        App::getOrm()->clear('Application\\DeskPRO\\Entity\\Ticket');
        App::getOrm()->clear('Application\\DeskPRO\\Entity\\TicketSla');

        unset($GLOBALS['DP_ESCALATION_RUNNING']);
    }
}
