<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace Application\DeskPRO\Command;

use Application\DeskPRO\EmailGateway\Reader\ValueReader;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Monolog\Logger as DpLogger;
use Application\DeskPRO\Tickets;
use Application\DeskPRO\Tickets\TicketSaveActions;
use Monolog\Handler\StreamHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Attempts to replay triggers for a newreply. Note that this isn't point-in-time reply
 * because the ticket state is loaded based on what it is now. For example,
 * triggers that work on "Foo is changed to xyz" won't work, because we don't have the
 * state recorder with the info about changes made during the action.
 */
class ReplayNewReplyTriggersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:replay-newreply-triggers');
        $this->addOption('run', null, InputOption::VALUE_NONE, 'Run the process (instead of preview)');
        $this->addOption('tz', 't', InputOption::VALUE_REQUIRED, 'Timezone, or UTC by default.');
        $this->addOption('reexecute', null, InputOption::VALUE_NONE, 'Re-execute triggers even if they ran already');
        $this->addArgument('range_start', InputArgument::REQUIRED, 'Datetime range (start)');
        $this->addArgument('range_end', InputArgument::REQUIRED, 'Datetime range (end)');
        $this->addArgument('trigger_ids', InputArgument::REQUIRED, 'Trigger IDs to replay');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tz         = new \DateTimeZone($input->getOption('tz') ?: 'UTC');
        $date_start = \DateTime::createFromFormat('Y-m-d H:i:s', $input->getArgument('range_start'), $tz);
        $date_end   = \DateTime::createFromFormat('Y-m-d H:i:s', $input->getArgument('range_end'), $tz);

        $do_run = $input->getOption('run');

        if (!$tz || !$date_start || !$date_end) {
            return 1;
        }

        $db = $this->getContainer()->getDb();
        $em = $this->getContainer()->getEm();

        $trigger_ids = explode(',', $input->getArgument('trigger_ids'));
        $trigger_ids = array_map('trim', $trigger_ids);

        $output->writeln('Timezone: '.$tz->getName());
        $output->writeln('Start:    '.$date_start->format('Y-m-d H:i:s'));
        $output->writeln('End:      '.$date_end->format('Y-m-d H:i:s'));
        $output->writeln('Triggers: '.implode(', ', $trigger_ids));

        $triggers = $em->createQuery('
            SELECT t
            FROM DeskPRO:TicketTrigger t
            WHERE t.id IN (?0) AND t.is_enabled = true
        ')->setParameter(0, $trigger_ids)->execute();

        if (!$triggers) {
            $output->writeln('No triggers to execute');
        }

        $ids = $db->fetchAllCol("
            SELECT id
            FROM tickets_logs
            WHERE action_type = 'message_created' AND date_created BETWEEN ? AND ?
            ORDER BY id ASC
        ", array($date_start->format('Y-m-d H:i:s'), $date_end->format('Y-m-d H:i:s')));

        $output->writeln('Number of new messages in the time period: '.count($ids));

        if (!$ids) {
            $output->writeln('Nothing to do!');

            return 0;
        }

        $do_rexec = $input->getOption('reexecute');

        foreach ($ids as $id) {
            $this->_run($id, $do_run, $triggers, $do_rexec, $output);
        }

        return 0;
    }

    /**
     * @param int             $id
     * @param bool            $do_run
     * @param TicketTrigger[] $triggers
     * @param bool            $do_rexec
     * @param OutputInterface $output
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    private function _run($id, $do_run, $triggers, $do_rexec, OutputInterface $output)
    {
        $db = $this->getContainer()->getDb();
        $em = $this->getContainer()->getEm();

        $log = $em->find('DeskPRO:TicketLog', $id);
        if (!$log || !$log->parent) {
            $output->writeln('Unknown ID: '.$id);

            return;
        }

        /** @var Ticket $ticket */
        $ticket        = $log->ticket;
        $action_person = $log->person;

        $group = $em->createQuery('
            SELECT l
            FROM DeskPRO:TicketLog l
            WHERE l.parent = ?0
        ')->setParameters(array($log->parent))->execute();

        $message_log     = null;
        $has_trigger_ids = array();
        foreach ($group as $l) {
            if ($l->trigger_id) {
                $has_trigger_ids[$l->trigger_id] = $l->trigger_id;
            }

            if ($l->action_type == 'message_created') {
                $message_log = $l;
            }
        }

        $output->writeln('----------');
        $output->writeln("Ticket {$ticket->id}");

        if (!$message_log) {
            $output->writeln('No message');

            return;
        }

        if ($do_rexec) {
            $try_triggers = $triggers;
        } else {
            $try_triggers = array_filter($triggers, function ($t) use ($has_trigger_ids) {
                return !isset($has_trigger_ids[$t->getId()]);
            });
        }

        $message = $em->find('DeskPRO:TicketMessage', $message_log->id_after);
        if (!$message) {
            $output->writeln('No message');

            return;
        }

        $output->writeln("Message {$message->id} by {$message->person->getDisplayContact()}");

        if (!$try_triggers) {
            $output->writeln('No triggers to run');

            return;
        }

        if (!$message_log || !$action_person) {
            $output->writeln('Is not a message-related email: '.$id);

            return;
        }

        if (!$message || $message->ticket->id != $log->ticket->id) {
            $output->writeln("Not a valid message on $id");

            return;
        }

        if (!$do_run) {
            $output->writeln('Preview mode, not running');

            return;
        }

        $action_details = $log->parent->getDetails();

        $logger = new DpLogger('tickets');
        $logger->enableSavedMessages();

        $stream = new StreamHandler('php://stdout');
        $logger->pushHandler($stream);

        $ticket->getStateChangeRecorder()->recordData('free', array('message' => 'Replaying triggers for reply event on message #'.$message->getId().' by '.$message->person->getDisplayContact()));
        $ticket->getStateChangeRecorder()->record('message', null, $message);

        $context = new Tickets\ExecutorContext($logger);
        $context->setEventType($action_details['event']);
        $context->setEventMethod($action_details['event_method']);
        $context->setEventPerformer($action_details['event_performer']);

        if ($action_details['person_id'] && $action_person) {
            $context->setPersonContext($action_person);
        }

        $repos           = new CustomTicketTriggerRepository();
        $repos->triggers = $try_triggers;

        $reader = new ValueReader();
        $context->getVars()->set('email_reader', $reader);

        $exec      = new TicketSaveActions\ExecTriggers($repos, new Tickets\Actions\ActionApplicator($this->getContainer()));
        $save_logs = new TicketSaveActions\SaveTicketLogs($this->getContainer()->getEm());

        $exec->processTicket($ticket, $context);
        $save_logs->processTicket($ticket, $context);

        $this->getContainer()->getEm()->flush();

        $search_updater = new Tickets\TicketSearchUpdater($this->getContainer()->getDb(), $ticket);
        $search_updater->update();

        $ticket->resetStateChangeRecorder();
        $ticket->__dp_last_process_save = $ticket->getStateChangeRecorder()->getStateVersion();

        $output->writeln("\tDone");
    }
}

class CustomTicketTriggerRepository extends \Application\DeskPRO\EntityRepository\TicketTrigger
{
    public $triggers;

    public function __construct()
    {
    }

    public function getTriggersForEventType($t)
    {
        return $this->triggers;
    }
}
