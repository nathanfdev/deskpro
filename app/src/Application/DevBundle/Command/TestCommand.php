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
		$deps = App::getSystemService('DepartmentData')->getRootNodes();

		foreach ($deps as $d) {
			echo sprintf("%d %s\n", $d->getId(), $d->getFullTitle());

			if ($d->getChildren()) {
				foreach ($d->getChildren() as $d2) {
					echo sprintf("%d %s\n", $d2->getId(), $d2->getFullTitle());
				}
			}
		}

		echo "\n";
		echo str_repeat("-", 40);
		echo "\n";

		$deps = App::getSystemService('DepartmentData')->getRootNodes();

		foreach ($deps as $d) {
			echo sprintf("%d %s\n", $d->getId(), $d->getFullTitle());

			if ($d->getChildren()) {
				foreach ($d->getChildren() as $d2) {
					echo sprintf("%d %s\n", $d2->getId(), $d2->getFullTitle());
				}
			}
		}

		echo "\n";
	}
}
