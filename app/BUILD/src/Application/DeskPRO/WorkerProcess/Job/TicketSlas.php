<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Tickets\Actions\ActionApplicator;
use Application\DeskPRO\Tickets\Slas\SlaClientMessageSender;
use Application\DeskPRO\Tickets\Slas\SlaProcessor;

/**
 * Handles SLA warn/fail updates.
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
            new SlaClientMessageSender(App::$container->getDb(), App::$container->get('event_dispatcher'))
        );

        $me = $this;
        $proc->setLimiterCallback(function () use ($me) {
            return $me->isPastTimeLimit();
        });

        $context_factory = function () {
            $context = App::$container->getTicketManager()->createSystemExecutorContext('slas');

            return $context;
        };

        $count_failed  = $proc->processAllFailed($context_factory, App::$container->getTicketManager());
        $count_warning = $proc->processAllWarning($context_factory, App::$container->getTicketManager());

        if ($count_warning || $count_failed) {
            $this->getLogger()->logInfo("SLA statuses updated. Failed: $count_failed, warning: $count_warning");
        }

        App::getOrm()->clear('Application\\DeskPRO\\Entity\\Ticket');
        App::getOrm()->clear('Application\\DeskPRO\\Entity\\TicketSla');

        unset($GLOBALS['DP_ESCALATION_RUNNING']);
    }
}
