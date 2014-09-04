<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\EmailGateway\Reader\EzcReader;
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
		$this->addOption('to', null, InputOption::VALUE_REQUIRED, 'The TO address to interpret the email to. If provided, the gateway will be determiend based on this.');
		$this->addOption('source', null, InputOption::VALUE_REQUIRED,  'ID of an existing source ID to re-process.');
		$this->addOption('file', null, InputOption::VALUE_OPTIONAL,  'Path to an email file to process. No filename is required if you are sending the file through standard input (e.g., piping).');
		$this->addOption('success-string', null, InputOption::VALUE_OPTIONAL,  'A special string to output in case of success (e.g., use as a trigger for external tool). Note that this command will return 0 on success, so you can use that instead.');
		$this->addOption('error-string', null, InputOption::VALUE_OPTIONAL,  'A special string to output in case of error (e.g., use as a trigger for external tool). Note that this command will return 1 on an error, so you can use that instead.');
		$this->addOption('enable-retries', null, InputOption::VALUE_NONE, 'If processing the message fails, enable retry scheduling instead of setting to "error".');
		$this->addOption('insert-only', null, InputOption::VALUE_NONE, 'Save the source with an inserted status (do not process right now)');
		$this->setHelp("Example usage with dp:gen-rand-email:\n\tphp cmd.php dp:gen-rand-email --from-email=\"user@example.com\" --to-email=\"gateway@example.com\" | php cmd.php dp:process-email --file");
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
		$success_string = $input->getOption('success-string');
		$error_string   = $input->getOption('error-string');
		$insert_only    = $input->getOption('insert-only');

		#----------------------------------------
		# Read/save source object
		#----------------------------------------

		if ($input->getOption('source')) {
			$source = $this->getContainer()->getEm()->find('DeskPRO:EmailSource', $input->getOption('source'));

			if (!$source) {
				$output->writeln("<error>Could not find source</error>");
				return 1;
			}

			$reader = new EzcReader();
			$reader->setRawSource($source['raw_source']);
			$account = $source->email_account;

			if (!$account) {
				$account = $this->findEmailAccountFrom($reader);
			}
		} else {

			if ($input->getOption('file')) {
				$raw_source = file_get_contents($input->getOption('file'));
			} else {
				$raw_source = '';
				while (!feof(STDIN)) {
					$raw_source .= fread(STDIN, 1024);
				}
			}

			$raw_source = trim($raw_source);
			if (!$raw_source) {
				$output->writeln("<error>No email source file provided</error>");
				return 1;
			}

			$raw_source = Strings::standardEol($raw_source);

			$header_end = strpos($raw_source, "\n\n");
			if ($header_end === false) {
				// Means an empty body (eg message with only subject)
				// But we trimmed above so the \n\n sep would be trimmed off
				$raw_source .= "\n\n";
				$header_end = strpos($raw_source, "\n\n");
			}

			$raw_headers = trim(substr($raw_source,0, $header_end));

			$reader = new EzcReader();
			$reader->setRawSource($raw_source);
			$account = $this->findEmailAccountFrom($reader);

			$source = new EmailSource();
			$source->fromArray(array(
				'email_account' => $account,
				'headers' => $raw_headers,
				'status' => 'inserted',
			));

			// Rough matching, just for info purposes when browsing a list
			$source->header_to      = Strings::extractRegexMatch('#^To:\s*(.*?)$#m', $raw_headers) ?: '';
			$source->header_from    = Strings::extractRegexMatch('#^From:\s*(.*?)$#m', $raw_headers) ?: '';
			$source->header_subject = Strings::extractRegexMatch('#^Subject:\s*(.*?)$#m', $raw_headers) ?: '';
			$source->object_type    = 'ticket';

			$t = microtime(true);
			$output->writeln("<info>Saving blob...</info>");

			$blob = App::getContainer()->getBlobStorage()->createBlobRecordFromString(
				$raw_source,
				'email.eml',
				'message/rfc822'
			);

			$source->blob = $blob;

			// Set the copied raw source or else $source->getRawSource() will
			// attempt to load it from the blob storage which is wasteful (eg could read back from s3 what we just wrote)
			$source->_raw = $raw_source;

			App::getOrm()->persist($source);
			App::getOrm()->flush();

			$output->writeln(sprintf("<info>Saved email source #" . $source->getId() . " (took %.5s)</info>", microtime(true) - $t));
		}

		#----------------------------------------
		# Get gateway account
		#----------------------------------------

		$account_id = $input->getOption('account');

		if (!$source->email_account && !$account_id) {
			$output->writeln("<error>Could not find account for email. Specify an account using --account</error>");
			return 1;
		}

		if ($account_id) {
			$account_manager = App::$container->getEmailAccountManager();

			if (ctype_digit($account_id)) {
				if ($account_manager->hasAcccount($account_id)) {
					$output->writeln("<error>No account with ID $account_id</error>");
					return 1;
				}

				$account = $account_manager->getAccount($account_id);
			} else {
				$account = $account_manager->findAccountForEmailAddress($account_id);

				if (!$account) {
					$output->writeln("<error>No account with address $account_id</error>");
					return 1;
				}
			}


			if ($input->getOption('account-force') && !$account->is_enabled) {
				$output->writeln("<error>Account $account_id is disabled (use --account-force if you want to use it anyway)</error>");
			}
		}

		$source->email_account = $account;

		#----------------------------------------
		# Run the gateway
		#----------------------------------------

		if (!$insert_only) {
			$output->setVerbosity(3);

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
				if ($success_string) {
					echo "\n";
					echo $success_string;
					echo "\n";
				}

				return 0;
			} else {
				if ($error_string) {
					echo "\n";
					echo $error_string;
					echo "\n";
				}

				return 1;
			}
		}
	}


	/**
	 * @param AbstractReader $reader
	 * @return \Application\DeskPRO\Entity\EmailAccount|null
	 */
	private function findEmailAccountFrom(AbstractReader $reader)
	{
		$account_manager = App::$container->getEmailAccountManager();

		foreach ($reader->getReceivedAddresses() as $email) {
			$account = $account_manager->findAccountForEmailAddress($email->email, 'is_enabled');
			if ($account) {
				return $account;
			}
		}

		return null;
	}
}