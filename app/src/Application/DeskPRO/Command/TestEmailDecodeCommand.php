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
use Application\DeskPRO\EmailGateway\Reader\EzcReader;
use Application\DeskPRO\EmailGateway\TicketGateway\AgentReplyCodes;
use Application\DeskPRO\EmailGateway\TicketGateway\TicketIncomingEmail;
use Application\DeskPRO\EmailGateway\TicketGateway\TicketIncomingEmailMessage;
use Orb\Util\Strings;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestEmailDecodeCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	/**
	 * @var \Application\DeskPRO\EmailGateway\Reader\EzcReader
	 */
	private $reader;

	/**
	 * @var string
	 */
	private $file;

	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dp:test-email-decode');

		$this->addArgument('file', InputArgument::REQUIRED, 'The email file to process');
		$this->addOption('source', null, InputOption::VALUE_NONE, 'The "file" is a source ID to process instead of a file on the filesystem');
		$this->addOption('no-cut', null, InputOption::VALUE_NONE, 'Do not run the cutters');
		$this->addOption('no-pattern-cut', null, InputOption::VALUE_NONE, 'Do not run the pattern cutters');
		$this->addOption('raw', null, InputOption::VALUE_NONE, 'Just output the raw decoded email');
		$this->addOption('force-text', null, InputOption::VALUE_NONE, 'Force use of text instead of HTML');
		$this->addOption('forward', null, InputOption::VALUE_NONE, 'Test splitting as a forwarded message');
		$this->addOption('reply-codes', null, InputOption::VALUE_NONE, 'Test reply codes');
		$this->addOption('save-attach', null, InputOption::VALUE_NONE, 'This will save attachments from the email in the same directory as the file');
		$this->addOption('show-cutters', null, InputOption::VALUE_NONE, 'Displays the cutters that were used');
		$this->addOption('output-text', null, InputOption::VALUE_NONE, 'Process an email as normal, but output as text (e.g., HTML will be stripped).');
		$this->addOption('output-attach', null, InputOption::VALUE_REQUIRED, 'Output the raw contents of an attachment at index');
		$this->addOption('output-attach-email', null, InputOption::VALUE_REQUIRED, 'Decode the attachment at index as an email');
		$this->addOption('output-log', null, InputOption::VALUE_NONE, 'Output logger info');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->file = $input->getArgument('file');

		if ($input->getOption('source')) {

			$source_obj = App::getOrm()->find('DeskPRO:EmailSource', $this->file);
			if (!$source_obj || !$source_obj->blob) {
				$output->writeln("<error>Invalid source ID</error>");
				return 1;
			}

			$source = App::getSystemService('BlobStorage')->copyBlobRecordToString($source_obj->blob);

		} else {
			if ($this->file && !is_file($this->file)) {
				if (is_file(getcwd() . '/' . $this->file)) {
					$this->file = getcwd() . '/' . $this->file;
				}
			}
			if (!$this->file || !is_file($this->file)) {
				$output->writeln("<error>Invalid file specified</error>");
				return 1;
			}

			$source = file_get_contents($this->file);
		}

		$r = new EzcReader();
		$r->setRawSource($source);
		$this->reader = $r;

		$output_attach       = $input->getOption('output-attach');
		$output_attach_email = $input->getOption('output-attach-email');

		if ($output_attach !== null) {
			return $this->outputAttachment($output_attach, $input, $output);
		} else if ($output_attach_email !== null) {
			return $this->outputAttachmentEmail($output_attach_email, $input, $output);
		} else {
			return $this->outputStandard($input, $output);
		}
	}

	####################################################################################################################

	private function outputStandard(InputInterface $input, OutputInterface $output)
	{
		$save_attach = $input->getOption('save-attach');

		$r = $this->reader;

		echo "Subject: " . $r->getSubject()->getSubjectUtf8();
		echo "\n";

		if ($r->getFromAddress()->getName()) {
			echo "From: " . $r->getFromAddress()->getName() . " <" . $r->getFromAddress()->getEmail() . ">";
		} else {
			echo "From: " . $r->getFromAddress()->getEmail();
		}
		echo "\n";

		foreach ($r->getToAddresses() as $email) {
			if ($email->getNameUtf8()) {
				echo "To: " . $email->getNameUtf8() . " <" . $email->getEmail() . ">";
			} else {
				echo "To: <" . $email->getEmail() . ">";
			}
			echo "\n";
		}

		foreach ($r->getCcAddresses() as $email) {
			if ($email->getNameUtf8()) {
				echo "CC: " . $email->getNameUtf8() . " <" . $email->getEmail() . ">";
			} else {
				echo "CC: <" . $email->getEmail() . ">";
			}
			echo "\n";
		}

		if ($date = $r->getDate()) {
			echo "Date: " . $date->format('Y-m-d H:i:s');
			echo "\n";
		}

		if ($attaches = $r->getAttachments()) {
			foreach ($attaches as $k => $attach) {
				if ($save_attach) {
					file_put_contents(dirname($this->file) . '/' . $k . '-' . $attach->getFileName(), $attach->getFileContents());
				}
				echo "Attachment[$k]: " . $attach->getFileName();
				echo "\n";
			}
		}

		echo "\n";

		if ($input->getOption('forward')) {
			$email_info = array();
			$email_info['subject'] = $r->getSubject()->subject;
			if ($email_info['body'] = $r->getBodyText()->getBodyUtf8()) {
				$email_info['body_is_html'] = false;
			} else {
				$email_info['body'] = $this->reader->getBodyHtml()->getBodyUtf8();
				$email_info['body_is_html'] = false;
				$email_info['body'] = \Orb\Util\Strings::html2Text($email_info['body']);
			}

			$cutter = \Application\DeskPRO\EmailGateway\Cutter\CutterDefFactory::getDef($r);
			$fwd_cutter = new \Application\DeskPRO\EmailGateway\Cutter\ForwardCutter($email_info['body'], $email_info['body_is_html'], $cutter);

			echo "IS VALID FORWARD: " . ($fwd_cutter->isValid() ? "TRUE" : "FALSE");
			echo "\n\n\n\n\n";

			$data = $fwd_cutter->getData();

			$data['message_body'] = $this->cleanBodyText($data['message_body']);
			$data['fwd_message_body'] = $this->cleanBodyText($data['fwd_message_body']);

			print_r($fwd_cutter->getData());

		} elseif ($input->getOption('reply-codes')) {

			$logger = new \Orb\Log\Logger();
			$ar_w = new \Orb\Log\Writer\ArrayWriter();
			$logger->addWriter($ar_w);

			if ($r->getBodyHtml()->getBodyUtf8() && !$input->getOption('force-text')) {
				$body = $r->getBodyHtml()->getBodyUtf8();
				echo "HTML BODY\n";
				echo str_repeat('-', 72) . "\n";
				echo $body;
				$rc = new AgentReplyCodes($body, true);
				$rc->setCleaner(App::$container->getInputCleaner());
				$rc->setLogger($logger);
				$reply_actions = $rc->getProperties();

				echo "\n\n\nCLEANED HTML BODY\n";
				echo str_repeat('-', 72) . "\n";
				echo $rc->getOrigBody();
			} else {
				$body = $r->getBodyText()->getBodyUtf8();
				echo "TEXT BODY\n";
				echo str_repeat('-', 72) . "\n";
				echo $body;
				$rc = new AgentReplyCodes($body, false);
				$reply_actions = $rc->getProperties();
			}

			echo "\n\n\nREPLY CODES LOG\n";
			echo str_repeat('-', 72) . "\n";
			echo $ar_w->getMessagesAsString();

			if ($reply_actions) {
				echo "\n\n\nNEW BODY\n";
				echo str_repeat('#', 72) . "\n";
				echo $rc->getNewBody();
			}

		} else {

			$logger = new \Orb\Log\Logger();
			$ar_w = new \Orb\Log\Writer\ArrayWriter();
			$logger->addWriter($ar_w);

			$ticket_email = new TicketIncomingEmail();
			$ticket_email->reader          = $this->reader;
			$ticket_email->is_bounce       = false;
			$ticket_email->email_body_html = $this->reader->getBodyHtml()->body_utf8;
			$ticket_email->email_body_text = $this->reader->getBodyText()->body_utf8;

			if ($input->getOption('force-text')) {
				if ($ticket_email->email_body_text) {
					$ticket_email->email_body_html = '';
				} else {
					$ticket_email->email_body_text = Strings::html2Text($ticket_email->email_body_html);
					$ticket_email->email_body_html = '';
				}
				if ($input->getOption('raw')) {
					echo $ticket_email->email_body_text;
					echo "\n";
				}
			} else {
				if ($input->getOption('raw')) {
					if ($ticket_email->email_body_html) {
						if ($input->getOption('output-text')) {
							echo Strings::html2Text($ticket_email->email_body_html);
						} else {
							echo $ticket_email->email_body_html;
						}
					} else {
						echo $ticket_email->email_body_text;
					}
				}
			}

			if ($input->getOption('no-cut')) {
				$ticket_email->force_no_reply_cutter = true;
			}
			if ($input->getOption('no-pattern-cut')) {
				$ticket_email->force_no_pattern_cutter = true;
			}

			$email_info = new TicketIncomingEmailMessage(
				TicketIncomingEmailMessage::MODE_NEWREPLY,
				null,
				$ticket_email,
				App::$container->getInputCleaner(),
				App::$container->getEmailAccountManager(),
				null,
				$logger
			);

			$body = $email_info->body;

			if ($input->getOption('output-text')) {
				$body = Strings::html2Text($body);
			}

			echo $body;
			echo "\n";

			if ($input->getOption('output-log')) {
				echo "\nLOG\n================================\n\n";
				echo $ar_w->getMessagesAsString();
				echo "\n";
			}
		}

		return 0;
	}

	private function cleanBodyText($text)
	{
		if ($this->reader->isOutlookMailer()) {
			$text = \Orb\Util\Strings::standardEol($text);
			$text = str_replace("\n\n", "\n", $text);
		}

		return $text;
	}

	####################################################################################################################

	private function outputAttachment($idx, InputInterface $input, OutputInterface $output)
	{
		$attaches = $this->reader->getAttachments();
		if (!isset($attaches[$idx])) {
			$output->writeln("Error: No attachment at index $idx");
			return 1;
		}

		echo $attaches[$idx]->getFileContents();
		return 0;
	}

	####################################################################################################################

	private function outputAttachmentEmail($idx, InputInterface $input, OutputInterface $output)
	{
		$attaches = $this->reader->getAttachments();
		if (!isset($attaches[$idx])) {
			$output->writeln("Error: No attachment at index $idx");
			return 1;
		}

		$attach = $attaches[$idx];

		$r = new EzcReader();
		$r->setProperty('override_from_charset', $attach->original_charset);
		$r->setRawSource($attach->getFileContents());
		$this->reader = $r;

		return $this->outputStandard($input, $output);
	}
}
