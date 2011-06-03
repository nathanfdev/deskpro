<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110603074147 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE `client_messages` DROP `for_client`");
			App::getDb()->exec("
				ALTER TABLE  `client_messages` ADD  `for_client` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `created_by_client` ,
				ADD  `for_person_id` INT NULL DEFAULT NULL AFTER  `for_client`
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
