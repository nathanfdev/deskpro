<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

                return 1;
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

                    return 1;
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

            $rawSource = Strings::standardEol($rawSource);

            $headerEnd = strpos($rawSource, "\n\n");
            if ($headerEnd === false) {
                // Means an empty body (eg message with only subject)
                // But we trimmed above so the \n\n sep would be trimmed off
                $rawSource .= "\n\n";
                $headerEnd = strpos($rawSource, "\n\n");
            }

            $rawHeaders = trim(substr($rawSource, 0, $headerEnd));

            if (isset($rawHeaders[4000])) {
                $rawHeaders = substr($rawHeaders, 0, 4000);
            }

            $reader = $this->getContainer()->getEmailEzcReaderFactory()->create();
            $reader->setRawSource($rawSource);
            $account = $this->findEmailAccountFrom($reader);

            $fromEmail = $reader->getFromAddress();
            $source    = new EmailSource();
            $source->fromArray([
                'email_account' => $account,
                'headers'       => $rawHeaders,
                'status'        => 'inserted',
                'from_email'    => $fromEmail ? $fromEmail->getEmail() : null,
            ]);

            // Rough matching, just for info purposes when browsing a list
            $source->header_to      = Strings::extractRegexMatch('#^To:\s*(.*?)$#m', $rawHeaders) ?: '';
            $source->header_cc      = Strings::extractRegexMatch('#^Cc:\s*(.*?)$#m', $rawHeaders) ?: '';
            $source->header_from    = Strings::extractRegexMatch('#^From:\s*(.*?)$#m', $rawHeaders) ?: '';
            $source->header_subject = Strings::extractRegexMatch('#^Subject:\s*(.*?)$#m', $rawHeaders) ?: '';
            $source->object_type    = 'ticket';

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
