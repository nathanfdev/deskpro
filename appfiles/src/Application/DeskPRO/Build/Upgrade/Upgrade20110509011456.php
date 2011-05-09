<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110509011456 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Insert default filters');

		try {
			App::getOrm()->beginTransaction();

			$data_reader = new \Application\DeskPRO\Install\InstallData('data.php');
			foreach ($data_reader->getAllForTag('create_filter') as $name => $php) {
				$this->output->writeln("Inserting filter $name");
				eval($php);
			}

			App::getOrm()->commit();
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
