<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111013084424 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE  `custom_def_ticket` ADD  `display_order` INT( 0 ) NOT NULL DEFAULT  '0'");
			App::getDb()->exec("ALTER TABLE  `custom_def_people` ADD  `display_order` INT( 0 ) NOT NULL DEFAULT  '0'");
			App::getDb()->exec("ALTER TABLE  `custom_def_organizations` ADD  `display_order` INT( 0 ) NOT NULL DEFAULT  '0'");

			App::getDb()->exec("ALTER TABLE `custom_def_ticket` CHANGE `handler_class` `handler_class` VARCHAR(255) NULL DEFAULT NULL");
			App::getDb()->exec("ALTER TABLE `custom_def_people` CHANGE `handler_class` `handler_class` VARCHAR(255) NULL DEFAULT NULL");
			App::getDb()->exec("ALTER TABLE `custom_def_organizations` CHANGE `handler_class` `handler_class` VARCHAR(255) NULL DEFAULT NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
