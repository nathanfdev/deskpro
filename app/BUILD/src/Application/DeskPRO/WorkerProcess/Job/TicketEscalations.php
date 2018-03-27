<?php

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Monolog\Handler\OrbLoggerAdapterHandler;
use Application\DeskPRO\Monolog\Logger;
use Application\DeskPRO\Tickets\Actions\ActionApplicator;
use Application\DeskPRO\Tickets\Escalations\EscalationExecutor;
use Application\DeskPRO\Tickets\Escalations\EscalationsRunner;
use Application\DeskPRO\Tickets\Escalations\EscalationTicketMatcher;
use Application\DeskPRO\Tickets\Escalations\EscalationTicketMatcherTest;

/**
 * Executes escalations.
 */
class TicketEscalations extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    /**
     * @var int
     */
    protected $count_success;

    /**
     * @var int
     */
    protected $count_failed;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $batch_size = 100;
        $time_limit = 200;

        $orb_adapter = new OrbLoggerAdapterHandler($this->getLogger());
        $logger      = new Logger('TicketTriggers');
        $logger->pushHandler($orb_adapter);

        if ($this->options->has('testEscalation')) {
            $find_id     = $this->options->get('testEscalation');
            $escalations = App::$container->getEm()->getRepository('DeskPRO:TicketEscalation')->getByIds([$find_id]);
            if (!count($escalations)) {
                $logger->warn("testEscalation: Escalation #$find_id does not exist");

                return;
            } else {
                $logger->info("testEscalation: Only running escalation #$find_id");
            }
        } else {
            $escalations = App::$container->getEm()->createQuery('
                SELECT e
                FROM DeskPRO:TicketEscalation e
                WHERE e.is_enabled = true
                ORDER BY e.date_last_run ASC
            ')->execute();

            if (!count($escalations)) {
                return;
            }
        }

        if ($this->options->has('testTickets')) {
            $ticket_ids = $this->options->get('testTickets');
            if (!is_array($ticket_ids)) {
                $ticket_ids = explode(',', $ticket_ids);
            }

            $tickets = App::$container->getEm()->getRepository('DeskPRO:Ticket')->getByIds($ticket_ids);

            if (!count($tickets)) {
                $logger->warn('testTickets: No tickets found: '.implode(',', $ticket_ids));

                return;
            } else {
                $logger->info('testTickets: Found tickets: '.implode(',', $ticket_ids));
            }

            $matcher = new EscalationTicketMatcherTest(App::$container->getEm(), App::$container->getDb());
            $matcher->setTickets($tickets);
        } else {
            $matcher = new EscalationTicketMatcher(App::$container->getEm(), App::$container->getDb());
        }

        if ($this->getContainer()->get('deskpro.app_env')->getConfig('settings.escalation_double_check')) {
            $matcher->enableDoubleCheck();
        }

        $matcher->setLogger($logger);

        $executor = new EscalationExecutor(App::$container->getDb(), App::$container->getTicketManager(), new ActionApplicator(App::$container));
        $executor->setLogger($logger);

        $runner = new EscalationsRunner(
            $escalations,
            $matcher,
            $executor,
            $batch_size,
            $time_limit
        );
        $runner->setLogger($logger);

        $GLOBALS['DP_ESCALATION_RUNNING'] = true;
        $runner->run();
        unset($GLOBALS['DP_ESCALATION_RUNNING']);
    }
}
