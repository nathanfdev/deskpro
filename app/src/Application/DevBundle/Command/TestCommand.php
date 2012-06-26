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
		$reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
		$em = $this->getContainer()->getEm();

		$detect = new \Application\DeskPRO\EmailGateway\TicketGateway\DetectInlineReply($em, $reader);

		$str1 = 'this is a reply this is a reply this is a reply this is a reply this is a reply this is a reply';
		$str2 = 'this is a reply this is a reply this is a reply this is a reply this is a reply this is a reply!!!!@!!!@@@@@£@£@£@£@£@£';

		var_dump($detect->getMessageDifference($str1, $str2));

		echo "\n";
	}
}
