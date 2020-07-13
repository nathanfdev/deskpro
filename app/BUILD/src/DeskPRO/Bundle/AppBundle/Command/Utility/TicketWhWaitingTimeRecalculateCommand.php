<?php

namespace DeskPRO\Bundle\AppBundle\Command\Utility;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\Ticket as TicketRepos;
use DeskPRO\Bundle\AppBundle\Ticket\TicketWhWaitingTimeCalculator;
use Orb\Util\WorkHoursSetAll;
use Orb\Util\WorkHoursInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Process\Process;
use Monolog\Logger;
use Doctrine\ORM\EntityManager;

class TicketWhWaitingTimeRecalculateCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     *
     * @var TicketRepos
     */
    private $ticketRepos;

    /**
     * @var WorkHoursInterface
     */
    private $wh;

    /**
     * @var Logger
     */
    private $logger;

    /**
     *
     * @var TicketWhWaitingTimeCalculator
     */
    private $calculator;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:utility:recalc-waiting-time')
            ->setDescription('Recalculate ticket waiting times in working hours (total_user_waiting_wh, total_to_first_reply_wh)')

            ->addOption('ticket-id', null, InputOption::VALUE_REQUIRED, 'A ticket ID to process (or the starting ID if running in a batch)')
            ->addOption('preview', null, InputOption::VALUE_NONE, 'Show the calculation but don\'t persist it.')

            ->addOption('wh-start', null, InputOption::VALUE_REQUIRED, 'Override working hours start time setting in HH:MM format.')
            ->addOption('wh-end', null, InputOption::VALUE_REQUIRED, 'Override working hours end time setting in HH:MM format.')
            ->addOption('wh-days', null, InputOption::VALUE_REQUIRED, 'Override working hour days. Comma-separated list of days 1-7 representing Mon-Sun (e.g. 1,2,3,4,5 would be M-F).')

            ->addOption('batch-mode', null, InputOption::VALUE_NONE, 'Run the command multiple times to process many tickets')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'How many tickets to run at a time')
            ->addOption('min-ticket-id', null, InputOption::VALUE_REQUIRED, ' The lowest ticket id to process if running in batch mode')
        ;
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() <= OutputInterface::VERBOSITY_NORMAL) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        } else {
            $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }

        $this->logger = new Logger('default');
        $this->logger->pushHandler(new ConsoleHandler($output));

        $this->wh = $this->getContainer()->get('work_hours_set_factory')->create(
            $input->getOption('wh-start', null),
            $input->getOption('wh-end', null),
            $input->getOption('wh-days', null)
        );
        $this->printWorkHoursSet();

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->ticketRepos = $this->em->getRepository(Ticket::class);
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $DP_ENV;

        $isPreview      = $input->getOption('preview');
        $startTicketId  = $input->getOption('ticket-id');
        $isBatchMode    = $input->getOption('batch-mode');
        $numTickets     = $input->getOption('batch-size') ?? ($isBatchMode ? 1000 : 1);
        $minTicketId    = max($input->getOption('min-ticket-id') ?? 0, 0);

        if (!$startTicketId) {
            $this->logger->error("Provide `ticket-id` option");
            return 1;
        }

        $this->logger->info("");
        $this->logger->info("Start Ticket Id: " . $startTicketId);
        $this->logger->info("Min Ticket Id: " . $minTicketId);
        $this->logger->info("Is batch mode: " . ($isBatchMode ? 1 : 0));
        $this->logger->info("Number of tickets to process: " . $numTickets);
        $this->logger->info("Is in preview mode: " . ($isPreview ? 1 : 0));
        $this->logger->info("");

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Confirm or press `n` to cancel (y/n) ?', true);

        if (!$helper->ask($input, $output, $question)) {
            return 0;
        }

        if ($isBatchMode) {
            while ($startTicketId > $minTicketId) {
                $cmd = [
                    'dp:utility:recalc-waiting-time',
                    '--ticket-id', $startTicketId,
                    '--min-ticket-id', $minTicketId,
                    '--batch-size', $numTickets,
                    '--no-interaction'
                ];
                if ($input->getOption('wh-start')) {
                    $cmd[] = '--wh-start';
                    $cmd[] = $input->getOption('wh-start');
                }
                if ($input->getOption('wh-end')) {
                    $cmd[] = '--wh-end';
                    $cmd[] = $input->getOption('wh-end');
                }
                if ($input->getOption('wh-days')) {
                    $cmd[] = '--wh-days';
                    $cmd[] = $input->getOption('wh-days');
                }
                if ($isPreview) {
                    $cmd[] = '--preview';
                }
                if ($output->getVerbosity() === OutputInterface::VERBOSITY_DEBUG) {
                    $cmd[] = '--verbose';
                }

                $process = new Process(
                    $this->getContainer()->get('deskpro.app_env')->getConsolePhpCommand($cmd),
                    $DP_ENV->getDpRoot()
                );
                $process->setTimeout(36000);
                $process->run(function ($type, $data) use ($output) {
                    if ($type === 'out') {
                        $output->write($data);
                    } else {
                        $output->write('<error>'.$data.'</error>');
                    }
                });

                if (!$process->isSuccessful()) {
                    $this->logger->error('<error>Sub call to command failed with error status</error>');
                }
                $startTicketId -= $numTickets;
            }
        } else {
            $currentBatchMinId = max($startTicketId - $numTickets + 1, $minTicketId);
            $ticketId = $startTicketId;
            while ($ticketId >= $currentBatchMinId) {
                $this->processTicketId($ticketId, $isPreview);
                --$ticketId;
            }
        }

        return 0;
    }

    protected function processTicketId($ticketId, $isPreview)
    {
        $this->logger->info('');
        $this->logger->info('Processing Ticket: ' . $ticketId);

        $ticket = $this->ticketRepos->find($ticketId);
        if (!$ticket) {
            $this->logger->info("Can't find Ticket");
            return;
        }

        $res = [];
        $isOk = $this->getCalculator()->calculate($ticket, $this->wh, $res);
        if (!$isOk) {
            return;
        }

        $this->logger->info('total_user_waiting_wh_start: ' . ($res['total_user_waiting_wh_start']
            ? $res['total_user_waiting_wh_start']->format('Y-m-d h:i:s')
            : 'null'));
        $this->logger->info('total_user_waiting_wh: ' . $res['total_user_waiting_wh']);
        $this->logger->info('total_to_first_reply_wh: ' . $res['total_to_first_reply_wh']);

        if (!$isPreview) {
            $this->logger->info('Update ticket values in DB');
            $this->em->getConnection()->update(
                'tickets',
                [
                    'total_user_waiting_wh_start' => $res['total_user_waiting_wh_start']
                                                     ? $res['total_user_waiting_wh_start']->format('Y-m-d H:i:s')
                                                     : null,
                    'total_user_waiting_wh' => $res['total_user_waiting_wh'],
                    'total_to_first_reply_wh' => $res['total_to_first_reply_wh'],
                ],
                [
                    'id' => $ticketId
                ]
            );
        }
    }

    /**
     *
     * @return TicketWhWaitingTimeCalculator
     */
    protected function getCalculator()
    {
        if (!$this->calculator) {
            $this->calculator = new TicketWhWaitingTimeCalculator($this->em, $this->logger);
        }

        return $this->calculator;
    }

    protected function printWorkHoursSet()
    {
        if ($this->wh instanceof WorkHoursSetAll) {
            $this->logger->info('Work hours set: WorkHoursSetAll');
        } else {
            $this->logger->info(sprintf(
                'Work hours set: Start: %02s:%02s. End: %02s:%02s. Days: %s. Timezone: %s',
                $this->wh->getWorkStartHour(),
                $this->wh->getWorkStartMinute(),
                $this->wh->getWorkEndHour(),
                $this->wh->getWorkEndMinute(),
                implode(',', array_map(function($day) {
                    return $day ? 1 : 0;
                }, $this->wh->getWorkDays())),
                $this->wh->getWorkTimezone()
            ));
        }
    }
}
