<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111006081524 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE  `custom_def_ticket` ADD  `is_enabled` TINYINT( 1 ) NOT NULL DEFAULT  '1'");
			App::getDb()->exec("ALTER TABLE  `custom_def_people` ADD  `is_enabled` TINYINT( 1 ) NOT NULL DEFAULT  '1'");
			App::getDb()->exec("ALTER TABLE  `custom_def_organizations` ADD  `is_enabled` TINYINT( 1 ) NOT NULL DEFAULT  '1'");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
