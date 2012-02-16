<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120113102321 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE `people` ADD `can_admin` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `is_agent`");
			App::getDb()->exec("ALTER TABLE `people` ADD `can_agent` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `can_admin`");
			App::getDb()->exec("ALTER TABLE `people` ADD `can_billing` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `is_admin`");
			App::getDb()->exec("ALTER TABLE `people` ADD `can_reports` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `can_billing`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
