<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120110115538 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Recreating filters');

		try {

			App::getDb()->exec("DELETE FROM ticket_filters WHERE sys_name IS NOT NULL");

			// Install data stuff
			$install_data = new \Application\InstallBundle\Install\InstallDataReader(DP_ROOT.'/src/Application/InstallBundle/Data/data.php');
			$em = App::getOrm();

			$em->beginTransaction();
			foreach ($install_data->getAllForTag('create_filter') as $php) {
				eval($php);
			}

			$em->flush();
			$em->commit();
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
