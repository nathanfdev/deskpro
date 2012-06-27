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

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$email = file_get_contents(DP_ROOT . '/src/Application/DevBundle/Resources/email-sources/re-outlook2.txt');
		$reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
		$reader->setRawSource($email);

		$raw_body = $reader->getBodyHtml()->getBodyUtf8();
		$body = $this->getContainer()->get('deskpro.core.input_cleaner')->clean($raw_body, 'html_fix');
		$cutter = new \Application\DeskPRO\EmailGateway\Cutter\PatternCutter();
		$pattern_config = new \Application\DeskPRO\Config\UserFileConfig('html-cut-patterns');
		$cutter->addPatterns($pattern_config->all());
		//$cutter->addPattern('p b span #from:#i /span /b span #.*# br /br b #sent:#i /b #.*# br /br b #to:#i /b #.*# br /br /span /p');
		//$cutter->addPattern('p b span #from:#i /span /b span #.*# br /br b /b /span /p');

		$time = microtime(true);


		$body = $cutter->cutQuoteBlock($body, true);
		$body = $this->getContainer()->get('deskpro.core.input_cleaner')->clean($body, 'html_email');

		$matched = $cutter->getMatchedPatterns();

		if ($matched) {
			echo $body;

			echo "\n\n\n\n";
			foreach ($matched as $p) {
				echo "Matched: ";
				echo $p->getPattern();
				echo "\n";
			}
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
