<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110328221220 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Change log_name and session_name on log table to bigger varchar fields');

		try {
			App::getDb()->exec("
				ALTER TABLE  `log_items` CHANGE  `log_name`  `log_name` VARCHAR( 255 ) NOT NULL,
				CHANGE  `session_name`  `session_name` VARCHAR( 255 ) NULL DEFAULT NULL
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
