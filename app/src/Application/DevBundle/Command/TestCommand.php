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
		$tickets = App::getEntityRepository('DeskPRO:Ticket')->getByIds(array(13206,13162,13120,13106,13081,13057,13013,12986,12978,12965,12890,12789,12652,12545,12522,12446));

		$pids = array();
		foreach ($tickets as $t) {
			$pids[] = $t->person->getId();
		}

		$people = App::getEntityRepository('DeskPRO:Person')->getByIds($pids);

		foreach ($tickets as $t) {
			echo $t->person->getDisplayName();
			echo "\n";
		}
	}
}
