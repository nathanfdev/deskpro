<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110322032515 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Rename field');

		try {
			App::getDb()->exec("ALTER TABLE  `agent_notifications` CHANGE  `queue_id`  `filter_id` INT( 11 ) NOT NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
