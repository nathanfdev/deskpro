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

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Util\Strings;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Route;

class TestEmailDecodeCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected $reader;

	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:test-email-decode');

		$this->addArgument('file', InputArgument::REQUIRED, 'The email file to process');
		$this->addOption('no-cut', null, InputOption::VALUE_NONE, 'Do not run the cutters');
		$this->addOption('raw', null, InputOption::VALUE_NONE, 'Just output the raw decoded email');
		$this->addOption('force-text', null, InputOption::VALUE_NONE, 'Force use of text instead of HTML');
		$this->addOption('forward', null, InputOption::VALUE_NONE, 'Test splitting as a forwarded message');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$file = $input->getArgument('file');
		if ($file && !is_file($file)) {
			if (is_file(getcwd() . '/' . $file)) {
				$file = getcwd() . '/' . $file;
			}
		}
		if (!$file || !is_file($file)) {
			$output->writeln("<error>Invalid file specified</error>");
			return 1;
		}

		$source = file_get_contents($file);

		$r = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
		$r->setRawSource($source);

		$this->reader = $r;


		echo "Subject: " . $r->getSubject()->getSubjectUtf8();
		echo "\n";

		foreach ($r->getToAddresses() as $email) {
			if ($email->getNameUtf8()) {
				echo "To: " . $email->getNameUtf8() . " <" . $email->getEmail() . ">\n";
			} else {
				echo "To: <" . $email->getEmail() . ">\n";
			}
		}
		echo "\n";

		foreach ($r->getCcAddresses() as $email) {
			if ($email->getNameUtf8()) {
				echo "CC: " . $email->getNameUtf8() . " <" . $email->getEmail() . ">\n";
			} else {
				echo "CC: <" . $email->getEmail() . ">\n";
			}
		}
		echo "\n";

		if ($attaches = $r->getAttachments()) {
			foreach ($attaches as $attach) {
				echo "Attachment: " . $attach->getFileName() . "\n";
			}
			echo "\n";
		}

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

				if ($input->getOption('raw')) {
					echo $body;
					echo "\n";
					return 0;
				}

				$body = $this->getContainer()->getIn()->getCleaner()->clean($body, 'html_email_preclean');

				if (!$input->getOption('no-cut')) {

					$generic_cutter = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
					$body = $generic_cutter->cutQuoteBlock($body, true);

					$cutter = new \Application\DeskPRO\EmailGateway\Cutter\PatternCutter();
					$pattern_config = new \Application\DeskPRO\Config\UserFileConfig('html-cut-patterns');
					$cutter->addPatterns($pattern_config->all());

					$body = $cutter->cutQuoteBlock($body, true);
					$body .= $generic_cutter->cutBottomBlock($raw_body, true);
				}

				$inline_image = new \Application\DeskPRO\EmailGateway\InlineImageTokens($r);
				$body = $inline_image->processTokens($body);
				$body = $this->getContainer()->getIn()->getCleaner()->clean($body, 'html_email_basicclean');
				$body = $this->getContainer()->getIn()->getCleaner()->clean($body, 'html_email');
				$body = Strings::trimHtmlAdvanced($body);

				foreach ($r->getAttachments() as $attach) {
					$body = $inline_image->replaceToken($attach->getContentId(), '<img>', $body);
				}

				$body = $this->getContainer()->getIn()->getCleaner()->clean($body, 'html_email_postclean');
			} else {
				$body = $r->getBodyText()->getBodyUtf8();

				if ($input->getOption('raw')) {
					echo $body;
					echo "\n";
					return 0;
				}

				if (!$input->getOption('no-cut')) {
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

	public function cleanBodyText($text)
	{
		if ($this->reader->isOutlookMailer()) {
			$text = \Orb\Util\Strings::standardEol($text);
			$text = str_replace("\n\n", "\n", $text);
		}

		return $text;
	}
}
