<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120118110912 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE `people` ADD `is_vacation_mode` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `can_reports`");
			App::getDb()->exec("ALTER TABLE `people` ADD `is_deleted` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `is_autoresponder`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
