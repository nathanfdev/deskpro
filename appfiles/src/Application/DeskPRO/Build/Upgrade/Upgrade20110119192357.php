<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110119192357 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Altering tables for group-by in queues');

		try {
			App::getDb()->exec("ALTER TABLE  `ticket_queues` ADD  `group_by` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `terms`");
			App::getDb()->exec("ALTER TABLE  `result_cache` ADD  `extra` LONGTEXT NOT NULL DEFAULT  '' AFTER  `results`");
		} catch (\Exception $e) {
			$this->output->writeln("Error: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
