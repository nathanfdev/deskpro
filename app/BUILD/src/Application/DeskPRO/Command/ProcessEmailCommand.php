<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\Email\EmailSource\PropertyMapper;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\EmailGateway\Runner;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Log\Logger;
use Orb\Util\Strings;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessEmailCommand extends ContainerAwareCommand
{
    const RET_MISSING_SOURCE = 64;
    const RET_EXPECT_PENDING = 65;

    protected function configure()
    {
        $this->setName('dp:process-email');
        $this->addOption('account', null, InputOption::VALUE_REQUIRED, 'ID or email address of the gateway to process the source under. -1 for default. If not provided, then the account will be detected based on the to/cc address.');
        $this->addOption('account-force', null, InputOption::VALUE_NONE, 'Use the account even if its disabled');
        $this->addOption('to', null, InputOption::VALUE_REQUIRED, 'Legacy option. Use --account instead.');
        $this->addOption('source', null, InputOption::VALUE_REQUIRED,  'ID of an existing source ID to re-process.');
        $this->addOption('file', null, InputOption::VALUE_OPTIONAL,  'Path to an email file to process. No filename is required if you are sending the file through standard input (e.g., piping).');
        $this->addOption('success-string', null, InputOption::VALUE_OPTIONAL,  'A special string to output in case of success (e.g., use as a trigger for external tool). Note that this command will return 0 on success, so you can use that instead.');
        $this->addOption('error-string', null, InputOption::VALUE_OPTIONAL,  'A special string to output in case of error (e.g., use as a trigger for external tool). Note that this command will return 1 on an error, so you can use that instead.');
        $this->addOption('enable-retries', null, InputOption::VALUE_NONE, 'If processing the message fails, enable retry scheduling instead of setting to "error".');
        $this->addOption('insert-only', null, InputOption::VALUE_NONE, 'Save the source with an inserted status (do not process right now)');
        $this->addOption('expect-pending', null, InputOption::VALUE_NONE, 'When used with --source, this ensures that the source is either "inserted" or "retry" states.');
        $this->setHelp("Example usage with dp:gen-rand-email:\n\tbin/console dp:gen-rand-email --from=\"user@example.com\" --to=\"gateway@example.com\" | bin/console dp:process-email --file");
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

        $successString = $input->getOption('success-string');
        $errorString   = $input->getOption('error-string');
        $insertOnly    = $input->getOption('insert-only');

        $expectPending = $input->getOption('expect-pending');

        if ($input->hasOption('to') && $input->getOption('to')) {
            $input->setOption('account', $input->getOption('to'));
        }

        //----------------------------------------
        // Read/save source object
        //----------------------------------------

        if ($input->getOption('source')) {
            /** @var EmailSource $source */
            $source = $this->getContainer()->getEm()->find(EmailSource::class, $input->getOption('source'));

            if (!$source) {
                $output->writeln('<error>Could not find source</error>');

                return self::RET_MISSING_SOURCE;
            }

            $reader = $this->getContainer()->getEmailEzcReaderFactory()->create();
            $reader->setEmailAccount($source->getEmailAccount());
            $reader->setRawSource($source['raw_source']);
            $account = $source->getEmailAccount();

            if (!$account) {
                $account = $this->findEmailAccountFrom($reader);
            }

            if ($expectPending) {
                if ($source->getStatus() !== EmailSource::STATUS_INSERTED && $source->getStatus() !== EmailSource::STATUS_RETRY) {
                    $output->writeln(sprintf('<error>Status is %s (expected inserted or retry)</error>', $source->getStatus()));

                    return self::RET_EXPECT_PENDING;
                }
            }

            // Mark as processing early
            $source->status = EmailSource::STATUS_PROCESSING;
            $this->getContainer()->getEm()->persist($source);
            $this->getContainer()->getEm()->flush();
        } else {
            if ($input->getOption('file')) {
                if (file_exists($input->getOption('file'))) {
                    $rawSource = file_get_contents($input->getOption('file'));
                } else {
                    $output->writeln('<error>File path does not exist: '.$input->getOption('file').'</error>');

                    return 1;
                }
            } else {
                $rawSource = '';
                while (!feof(STDIN)) {
                    $rawSource .= fread(STDIN, 1024);
                }
            }

            $rawSource = trim($rawSource);
            if (!$rawSource) {
                $output->writeln('<error>No email source file provided</error>');

                return 1;
            }

            $accountManager = $this->getContainer()->getEmailAccountManager();
            $readerFactory = $this->getContainer()->getEmailEzcReaderFactory();
            $mapper = new PropertyMapper($accountManager, $readerFactory);

            $reader = $mapper->createReader($rawSource);
            $source = $mapper->read($reader, new EmailSource());
            $source->fromArray([
                'status'         => EmailSource::STATUS_INSERTED,
                'object_type'    => EmailSource::OBJ_TYPE_TICKET,
            ]);

            $t = microtime(true);
            $output->writeln('<info>Saving blob...</info>');

            $blob = $this->getContainer()->getBlobStorage()->createBlobRecordFromString(
                $rawSource,
                'email.eml',
                'message/rfc822'
            );

            $source->blob = $blob;

            // Set the copied raw source or else $source->getRawSource() will
            // attempt to load it from the blob storage which is wasteful (eg could read back from s3 what we just wrote)
            $source->_raw = $rawSource;

            $this->getContainer()->getEm()->persist($source);
            $this->getContainer()->getEm()->flush();

            $output->writeln(sprintf('<info>Saved email source #'.$source->getId().' (took %.5s)</info>', microtime(true) - $t));
        }

        //----------------------------------------
        // Get gateway account
        //----------------------------------------

        $accountId = $input->getOption('account');

        if (!$source->email_account && !$accountId) {
            $output->writeln('<error>Could not find account for email. Specify an account using --account</error>');

            $source->status     = 'error';
            $source->error_code = 'invalid_address';
            $this->getContainer()->getEm()->persist($source);
            $this->getContainer()->getEm()->flush();

            return 1;
        }

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

                if (!$account) {
                    $output->writeln("<error>No account with address $accountId</error>");

                    $source->status     = 'error';
                    $source->error_code = 'invalid_address';
                    $this->getContainer()->getEm()->persist($source);
                    $this->getContainer()->getEm()->flush();

                    return 1;
                }
            }

            if ($input->getOption('account-force') && !$account->is_enabled) {
                $output->writeln("<error>Account $accountId is disabled (use --account-force if you want to use it anyway)</error>");

                $source->status     = 'error';
                $source->error_code = 'invalid_address';
                $this->getContainer()->getEm()->persist($source);
                $this->getContainer()->getEm()->flush();
            }
        }

        if ($source->email_account !== $account) {
            $source->email_account = $account;
            $this->getContainer()->getEm()->persist($source);
            $this->getContainer()->getEm()->flush();
        }

        //----------------------------------------
        // Run the gateway
        //----------------------------------------

        if (!$insertOnly) {
            $logger = new Logger();
            $logger->addWriter(new \Orb\Log\Writer\ConsoleOutputWriter($output));
            $logger->addFilter(new \Orb\Log\Filter\SimpleLineFormatter());

            $runner = new Runner();
            $runner->setLogger($logger);
            $runner->setPhpTimeLimit(900);
            if ($input->getOption('enable-retries') || defined('DP_EMAILPROC_ALWAYS_RETRY')) {
                $runner->setRetryScheduling(true);
            } else {
                $runner->setRetryScheduling(false);
            }
            $result = $runner->executeSource($source, $reader);

            if ($result) {
                if ($successString) {
                    echo "\n";
                    echo $successString;
                    echo "\n";
                }

                return 0;
            } else {
                if ($errorString) {
                    echo "\n";
                    echo $errorString;
                    echo "\n";
                }

                return 1;
            }
        } else {
            /* @var \DpRun\DpEnv $DP_ENV */
            global $DP_ENV;

            if ($DP_ENV->getConfig('async_email_processing.process')) {
                /** @var \Application\EmailBundle\Incoming\ProcQueue\ProcQueueInterface $proc */
                $proc = $this->getContainer()->get('in_email.proc_queue');
                $proc->enqueueNewEmail($source);
            }
        }

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
