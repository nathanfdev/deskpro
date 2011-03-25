<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110325040337 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Rename timezome to timezone');

		try {
			App::getDb()->exec("ALTER TABLE  `people` CHANGE  `timezome`  `timezone` VARCHAR( 50 )");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
