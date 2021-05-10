<?php



namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TicketTrigger;
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

        $contextFactory = function () {
            return App::$container->getTicketManager()->createSystemExecutorContext(TicketTrigger::EVENT_TYPE_SLAS);
        };

        $countFailed  = $proc->processAllFailed($contextFactory, App::$container->getTicketManager());
        $countWarning = $proc->processAllWarning($contextFactory, App::$container->getTicketManager());

        if ($countWarning || $countFailed) {
            $this->getLogger()->logInfo("SLA statuses updated. Failed: $countFailed, warning: $countWarning");
        }

        App::getOrm()->clear('Application\\DeskPRO\\Entity\\Ticket');
        App::getOrm()->clear('Application\\DeskPRO\\Entity\\TicketSla');

        unset($GLOBALS['DP_ESCALATION_RUNNING']);
    }
}
