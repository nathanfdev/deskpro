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

		$body = $reader->getBodyHtml()->getBodyUtf8();

		$pattern = 'div p ?a b span #from:#i /span /b span #.*# br /br b #sent:#i /b #.*# br /br b #to:#i /b #.*# br /br /span /p /div';
		$matcher = new \Application\DeskPRO\EmailGateway\Cutter\PatternCutter\HtmlMatcher($body, $pattern);
		$body = $matcher->getCutBody();
		$body = $this->getContainer()->get('deskpro.core.input_cleaner')->clean($body, 'html_email');

		echo $body;
	}
}
