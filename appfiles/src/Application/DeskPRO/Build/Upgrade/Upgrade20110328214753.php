<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110328214753 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Insert worker jobs');

		try {
			App::getOrm()->beginTransaction();

			$data_reader = new \Application\DeskPRO\Install\InstallData('data.php');
			foreach ($data_reader->getAllForTag('create_jobs') as $name => $php) {
				$this->output->writeln("Inserting job $name");
				eval($php);
			}

			App::getOrm()->commit();
		} catch (\Exception $e) {

			App::getOrm()->rollback();

			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
