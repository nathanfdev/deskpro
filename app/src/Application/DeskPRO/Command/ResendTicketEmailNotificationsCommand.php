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

use Application\DeskPRO\Tickets\TicketEmailBuilder;
use Orb\Util\Arrays;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResendTicketEmailNotificationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:email:resend-ticket-notifications');
        $this->addOption('run', null, InputOption::VALUE_NONE, 'Run the process (instead of preview)');
        $this->addOption('tz', 't', InputOption::VALUE_REQUIRED, 'Timezone, or UTC by default.');
        $this->addArgument('range_start', InputArgument::REQUIRED, 'Datetime range (start)');
        $this->addArgument('range_end', InputArgument::REQUIRED, 'Datetime range (end)');
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

        $output->writeln('Timezone: '.$tz->getName());
        $output->writeln('Start:    '.$date_start->format('Y-m-d H:i:s'));
        $output->writeln('End:      '.$date_end->format('Y-m-d H:i:s'));

        $db = $this->getContainer()->getDb();
        $em = $this->getContainer()->getEm();

        $ids = $db->fetchAllCol("
            SELECT id
            FROM tickets_logs
            WHERE action_type = 'ticket_email' AND date_created BETWEEN ? AND ?
            ORDER BY id ASC
        ", array($date_start->format('Y-m-d H:i:s'), $date_end->format('Y-m-d H:i:s')));

        $output->writeln('Number of emails in the time period: '.count($ids));

        if (!$ids) {
            $output->writeln('Nothing to do!');

            return 0;
        }

        foreach ($ids as $id) {
            $this->_run($id, $do_run, $output);
        }

        return 0;
    }

    private function _run($id, $do_run, OutputInterface $output)
    {
        $db = $this->getContainer()->getDb();
        $em = $this->getContainer()->getEm();

        $log = $em->find('DeskPRO:TicketLog', $id);
        if (!$log) {
            $output->writeln('Unknown ID: '.$id);

            return;
        }

        $ticket = $log->ticket;

        $group = $em->createQuery('
            SELECT l
            FROM DeskPRO:TicketLog l
            WHERE l.parent = ?0
        ')->setParameters(array($log->parent))->execute();

        $message_log = null;
        foreach ($group as $l) {
            if ($l->action_type == 'message_created') {
                $message_log = $l;
                break;
            }
        }

        if (!$message_log) {
            $output->writeln('Is not a message-related email: '.$id);

            return;
        }

        $message = $em->find('DeskPRO:TicketMessage', $message_log->id_after);
        if (!$message || $message->ticket->id != $log->ticket->id) {
            $output->writeln("Not a valid message on $id");

            return;
        }

        $user_mode  = $log->details['user_mode'];
        $to_email   = $log->details['to_email'];
        $to_ccs     = $log->details['cc_emails'];
        $from_email = $log->details['from_email'];
        $template   = $log->details['template'];

        $to_person = $em->getRepository('DeskPRO:Person')->findOneByEmail($to_email);
        if (!$to_person) {
            $output->writeln('ERROR: Unknow person for email '.$to_email." on log $id");

            return;
        }

        $output->writeln('Email for log '.$id.' for message '.$message->id.' by '.$message->person->display_name);
        $output->writeln("\tMode: $user_mode");
        $output->writeln("\tTo: $to_email :: ".$to_person->display_name);
        if ($to_ccs) {
            $output->writeln("\tCC: ", implode(',', $to_ccs));
        }
        $output->writeln("\tFrom: $from_email");
        $output->writeln("\tTpl: $template");

        $from_account = $this->getContainer()->getEmailAccountManager()->findAccountForEmailAddress($from_email);
        if (!$from_account) {
            $output->writeln('ERROR: Unknown email account for '.$from_email);

            return;
        }

        $new_replies = array($message);
        $ticket_logs = $group;

        $vars = array(
            'type'               => 'newreply',
            'performer_type'     => $message->person,
            'is_new_ticket'      => false,
            'is_new_agent_reply' => $message->person->is_agent && !$message->is_agent_note,
            'is_new_agent_note'  => $message->person->is_agent && $message->is_agent_note,
            'is_new_user_reply'  => !$message->person->is_agent,
            'is_status_change'   => false,
            'action_performer'   => $message->person,
            'new_message'        => Arrays::getLastItem($new_replies),
            'new_messages'       => $new_replies,
            'ticket_logs'        => $ticket_logs,
            'user_vars'          => array(),
        );

        if ($user_mode == 'agent') {
            $vars['type_flag'] = null;

            $ticket_email = TicketEmailBuilder::createFromContainer($this->getContainer())
                ->setTicket($ticket)
                ->setToPerson($to_person)
                ->setFromName($log->details['from_name'])
                ->setFromEmailAccount($from_account)
                ->setAgentMode()
                ->setTemplateName($template)
                ->setMaxAttachSize($this->getContainer()->getSetting('core.sendemail_attach_maxsize'))
                ->buildTicketEmail();

            if ($do_run) {
                try {
                    $ticket_email->send($vars);
                } catch (\Exception $e) {
                    $output->writeln("ERROR: {$e->getMessage()}");
                }
            }
        } else {
            $build = TicketEmailBuilder::createFromContainer($this->getContainer())
                ->setTicket($ticket)
                ->setToPerson($ticket->person)
                ->setUserMode()
                ->setTemplateName($template)
                ->setFromName($log->details['from_name'])
                ->setMaxAttachSize($this->getContainer()->getSetting('core.sendemail_attach_maxsize'))
                ->setFromEmailAccount($from_account);

            if ($to_ccs) {
                $build->enableUserCc();
            }

            if ($ticket->person === $to_person) {
                $build->setIsAuto();
            }

            $ticket_email = $build->buildTicketEmail();

            if ($do_run) {
                try {
                    $ticket_email->send($vars);
                } catch (\Exception $e) {
                    $output->writeln("ERROR: {$e->getMessage()}");
                }
            }
        }

        $output->writeln("\tDone");
    }
}
