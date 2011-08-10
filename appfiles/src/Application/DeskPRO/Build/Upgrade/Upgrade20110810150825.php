<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110810150825 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add email to tickets_messages table');

		try {
			App::getDb()->exec("ALTER TABLE  `tickets_messages` ADD  `email` VARCHAR( 255 ) NOT NULL AFTER  `ip_address`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
