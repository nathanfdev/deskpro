<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110506170746 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Remove old data from macros and filters');

		try {
			App::getDb()->exec("TRUNCATE TABLE ticket_macros");
			App::getDb()->exec("TRUNCATE TABLE ticket_filters");
			App::getDb()->exec("TRUNCATE TABLE ticket_filters_perms");
			App::getDb()->exec("TRUNCATE TABLE department_ticket_display");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
