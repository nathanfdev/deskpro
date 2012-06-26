<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$email = file_get_contents(DP_ROOT . '/src/Application/DevBundle/Resources/email-sources/re-sparrow.txt');
		$reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
		$reader->setRawSource($email);

		$raw_body = $reader->getBodyHtml()->getBodyUtf8();
		$body = $this->getContainer()->get('deskpro.core.input_cleaner')->clean($raw_body, 'html_fix');
		$body = $body;

		$cutter = new \Application\DeskPRO\EmailGateway\Cutter\PatternCutter();
		$pattern_config = new \Application\DeskPRO\Config\UserFileConfig('html-cut-patterns');
		$cutter->addPatterns($pattern_config->all());

		$time = microtime(true);

		$body = $cutter->cutQuoteBlock($body, true);
		$body = $this->getContainer()->get('deskpro.core.input_cleaner')->clean($body, 'html_email');

		$matched = $cutter->getMatchedPattern();
		if ($matched) {
			echo $body;

			echo "\n\n\n\n";
			echo "Matched: ";
			echo $matched->getPattern();
			echo "\n";
		} else {
			echo $raw_body;

			echo "\n\n\n\n";
			echo "NO MATCH";
			echo "\n\n";
		}

		echo sprintf("Took %.04f seconds\n", microtime(true) - $time);
	}
}
