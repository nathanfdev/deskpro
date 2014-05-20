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
		$this->addOption('raw', null, InputOption::VALUE_NONE, 'Just output the raw decoded email');
		$this->addOption('force-text', null, InputOption::VALUE_NONE, 'Force use of text instead of HTML');
		$this->addOption('convert-text', null, InputOption::VALUE_NONE, 'Convert HTML email into text');
		$this->addOption('forward', null, InputOption::VALUE_NONE, 'Test splitting as a forwarded message');
		$this->addOption('save-attach', null, InputOption::VALUE_NONE, 'This will save attachments from the email in the same directory as the file');
		$this->addOption('show-cutters', null, InputOption::VALUE_NONE, 'Displays the cutters that were used');
		$this->addOption('output-attach', null, InputOption::VALUE_REQUIRED, 'Output the raw contents of an attachment at index');
		$this->addOption('output-attach-email', null, InputOption::VALUE_REQUIRED, 'Decode the attachment at index as an email');
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

		} else {
			if ($r->getBodyHtml()->getBodyUtf8() && !$input->getOption('force-text')) {
				$body = $raw_body = $r->getBodyHtml()->getBodyUtf8();

				if ($input->getOption('convert-text')) {
					$text = Strings::standardEol($body);
					$text = str_replace("\n", ' ', $text);
					$text = preg_replace('#<br/?>#', "<br/>\n", $text);
					$text = preg_replace('#(<div[^>]+>)#', "$1\n", $text);
					$text = preg_replace('#(<p[^>]+>)#', "$1\n", $text);
					$text = preg_replace('#</div>#', "</div>\n", $text);
					$text = preg_replace('#</p>#', "</p>\n", $text);
					$text = strip_tags($text);
					echo $text;
					echo "\n";
					return 0;
				}

				if ($input->getOption('raw')) {
					echo $body;
					echo "\n";
					return 0;
				}

				if (!$input->getOption('no-cut')) {

					$generic_cutter = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
					$body = $generic_cutter->cutQuoteBlock($body, true);

					$cutter = new \Application\DeskPRO\EmailGateway\Cutter\PatternCutter();
					$pattern_config = new \Application\DeskPRO\Config\UserFileConfig('html-cut-patterns');
					$cutter->addPatterns($pattern_config->all());

					$body = $cutter->cutQuoteBlock($body, true);
					$body .= $generic_cutter->cutBottomBlock($raw_body, true);

					if ($input->getOption('show-cutters')) {
						$got = $cutter->getMatchedPatterns();
						if ($got) {
							foreach ($got as $p) {
								echo "[Matched Cutter] {$p->getPattern()}\n";
							}
						}
					}
				}

				$inline_image = new \Application\DeskPRO\EmailGateway\InlineImageTokens($r);
				$body = $inline_image->processTokens($body);

				$body = $this->getContainer()->getIn()->getCleaner()->clean($body, 'html_email_preclean');
				$body = $this->getContainer()->getIn()->getCleaner()->clean($body, 'html_email_basicclean');
				$body = $this->getContainer()->getIn()->getCleaner()->clean($body, 'html_email');
				$GLOBALS['doit'] = 1;
				$body = Strings::trimHtmlAdvanced($body);
				$body = $this->getContainer()->getIn()->getCleaner()->clean($body, 'html_email_postclean');

				foreach ($r->getAttachments() as $attach) {
					$body = $inline_image->replaceToken($attach->getContentId(), '<img>', $body);
				}
			} else {
				$body = $r->getBodyText()->getBodyUtf8();

				if ($input->getOption('raw')) {
					echo $body;
					echo "\n";
					return 0;
				}

				if (!$input->getOption('no-cut')) {
					$generic_cutter = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
					$body = $generic_cutter->cutQuoteBlock($body, false);

					$cutter = new \Application\DeskPRO\EmailGateway\Cutter\TextPatternCutter();
					$pattern_config = new \Application\DeskPRO\Config\UserFileConfig('text-cut-patterns');
					$cutter->addPatterns($pattern_config->all());
					$body = $cutter->cutQuoteBlock($body, false);
				}
			}

			echo $body;
			echo "\n";
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
