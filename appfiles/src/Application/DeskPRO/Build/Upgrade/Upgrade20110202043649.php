<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110202043649 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Alter ticket_categories tables');

		try {
			App::getDb()->exec("ALTER TABLE  `ticket_categories` ADD  `parent_id` AFTER `id` INT NULL DEFAULT NULL");
			App::getDb()->exec("UPDATE ticket_categories SET parent_id = FLOOR(1 + (RAND() * 4)) WHERE id > 4");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Alter tickets tables');

		try {
			App::getDb()->exec("ALTER TABLE  `tickets` CHANGE  `locked_by_agent`  `locked_by_agent` INT( 11 ) NULL DEFAULT NULL");
			App::getDb()->exec("UPDATE tickets SET locked_by_agent = NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
