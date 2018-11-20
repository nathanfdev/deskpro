<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\EmailSource;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ProcessEmailBacklogCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:process-email-backlog');
        $this->addOption('account', null, InputOption::VALUE_REQUIRED, 'ID or email address of a specific account');
        $this->addOption('limit', null, InputOption::VALUE_NONE, 'Stop after this many email have been processed');
        $this->addOption('time', null, InputOption::VALUE_NONE, 'Stop after this many seconds have ellapsed');
        $this->addOption('keep-alive', null, InputOption::VALUE_NONE, 'Continue even if there is no backlog. This keeps the process goign until limit/time is exceeded, or the processes is cancelled manually.');
        $this->addOption('enable-retries', null, InputOption::VALUE_NONE, 'If processing the message fails, enable retry scheduling instead of setting to "error".');
        $this->addOption('ignore-error', null, InputOption::VALUE_NONE, 'Continue processing the queue even if an exception occurs.');
        $this->setHelp('Processes all email sources marked as inserted or pending.');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $accountId = $input->getOption('account');
        $account   = null;

        $limitCount = (int) $input->getOption('limit') ?: 0;
        $limitTime  = (int) $input->getOption('time') ?: 0;

        $totalCount = -1;
        $totalTime  = 0;
        $startTime  = time();

        $keepAlive = $input->getOption('keep-alive');

        $appEnv = $this->getContainer()->get('deskpro.app_env');
        $db     = $this->getContainer()->getDb();

        if ($accountId) {
            $accountManager = $this->getContainer()->getEmailAccountManager();

            if (ctype_digit($accountId)) {
                if (!$accountManager->hasAcccount($accountId)) {
                    $output->writeln("<error>No account with ID $accountId</error>");

                    return 1;
                }

                $account = $accountManager->getAccount($accountId);
            } else {
                $account = $accountManager->findAccountForEmailAddress($accountId);
            }
        }

        while (true) {
            ++$totalCount;
            $totalTime = time() - $startTime;

            if ($limitCount && $totalCount > $limitCount) {
                $output->writeln("Stopping -- limitCount $limitCount reached");
                break;
            }

            if ($limitTime && $totalTime > $limitTime) {
                $output->writeln("Stopping -- limitTime $limitTime reached");
                break;
            }

            if ($account) {
                $nextId = $db->fetchColumn('
                    SELECT id FROM email_sources
                    WHERE status IN (?) AND email_account_id = ?
                    ORDER BY id ASC
                    LIMIT 1
                ', [['inserted', 'retry'], $account->getId()], 0, [Connection::PARAM_STR_ARRAY, \Pdo::PARAM_INT]);
            } else {
                $nextId = $db->fetchColumn('
                    SELECT id FROM email_sources
                    WHERE status IN (?)
                    ORDER BY id ASC
                    LIMIT 1
                ', [['inserted', 'retry']], 0, [Connection::PARAM_STR_ARRAY]);
            }

            if (!$nextId) {
                if ($keepAlive) {
                    sleep(2);
                    continue;
                } else {
                    break;
                }
            }

            $cmdParams = [
                'dp:process-email',
                '--source', $nextId,
                '--expect-pending',
            ];

            if ($input->getOption('enable-retries')) {
                $cmdParams[] = '--enable-retries';
            }

            $cmd = $appEnv->getConsolePhpCommand($cmdParams);
            $output->writeln("<info>EmailSource# {$nextId}</info>");
            $output->writeln('<info>$ '.$cmd.'</info>');

            $process = new Process($cmd);
            $process->setTimeout(600);
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo '[ERR] '.$buffer;
                } else {
                    echo $buffer;
                }
            });

            if (!$process->isSuccessful()) {
                $code = $process->getExitCode();
                $output->writeln("<error>Process exited with error status: $code</error>");

                switch ($code) {
                    case ProcessEmailCommand::RET_EXPECT_PENDING:
                        $output->writeln('<info>Expected pending. Perhaps another process was processing this email?</info>');
                        break;

                    case ProcessEmailCommand::RET_MISSING_SOURCE:
                        $output->writeln('<info>The source ID is missing. Perhaps it was deleted in another process?</info>');
                        break;

                    default:
                        if ($input->getOption('ignore-error')) {
                            $output->writeln('<info>Ingoring the error. The process continues.');
                        }
                        break;
                }
            } else {
                $output->writeln('Command successful');
            }
        }

        $output->writeln("Processed $totalCount sources");

        return 0;
    }

    /**
     * @param AbstractReader $reader
     *
     * @return \Application\DeskPRO\Entity\EmailAccount|null
     */
    private function findEmailAccountFrom(AbstractReader $reader)
    {
        $accountManager = $this->getContainer()->getEmailAccountManager();

        foreach ($reader->getReceivedAddresses() as $email) {
            $account = $accountManager->findAccountForEmailAddress($email->email, 'is_enabled');
            if ($account) {
                return $account;
            }
        }

        return null;
    }
}
