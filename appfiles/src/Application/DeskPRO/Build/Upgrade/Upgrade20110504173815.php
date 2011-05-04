<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110504173815 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add fields to ticket_triggers');

		try {
			App::getDb()->exec("
				ALTER TABLE  `ticket_triggers` ADD  `sys_name` VARCHAR( 50 ) NULL DEFAULT NULL ,
				ADD  `has_urgency` TINYINT( 1 ) NOT NULL DEFAULT  '0'
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
