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
		$email = file_get_contents(DP_ROOT . '/src/Application/DevBundle/Resources/email-sources/re-outlook.txt');
		$reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
		$reader->setRawSource($email);

		$raw_body = $reader->getBodyHtml()->getBodyUtf8();
		$body = $this->getContainer()->get('deskpro.core.input_cleaner')->clean($raw_body, 'html_fix');
		$body = $raw_body;
		$raw_body = <<<STR
<div>
	<p class=MsoNormal>
		<b>
			<span lang=EN-US style='font-size:10.0pt;font-family:"Tahoma","sans-serif"'>
				From:
			</span>
		</b>
		<span lang=EN-US style='font-size:10.0pt;font-family:"Tahoma","sans-serif"'>
			DeskPRO [mailto:hello@deskpro.com]
			<br></br>
			<b>Sent:</b>
			18 June 2012 10:42
			<br></br>
			<b>To:</b>
			Christopher Padfield
			<br></br>
			<b>Subject:</b>
			New Reply: Test Subject - 2012-06-18 10:36:27
		</span>
		</p>
</div>
STR;
		$body = $raw_body;

		$cutter = new \Application\DeskPRO\EmailGateway\Cutter\PatternCutter();
		//$pattern_config = new \Application\DeskPRO\Config\UserFileConfig('html-cut-patterns');
		//$cutter->addPatterns($pattern_config->all());
		//$cutter->addPattern('p b span #from:#i /span /b span #.*# br /br b #sent:#i /b #.*# br /br b #to:#i /b #.*# br /br /span /p');
		$cutter->addPattern('p b span #from:#i /span /b span #.*# br /br b /b /span /p');

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
