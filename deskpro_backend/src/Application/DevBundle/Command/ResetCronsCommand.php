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


/**
 * This just clears the worker job table and re-inserts everything from the data.php file
 */
class ResetCronsCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(

		))->setName('dpdev:reset-crons');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		App::getDb()->executeUpdate("TRUNCATE TABLE worker_jobs");

		$install_data = new \Application\InstallBundle\Install\InstallDataReader(DP_ROOT.'/src/Application/InstallBundle/Data/data.php');
		$em = App::getOrm();
		foreach ($install_data->getAllForTag('create_jobs') as $php) {
			eval($php);
		}
	}
}
