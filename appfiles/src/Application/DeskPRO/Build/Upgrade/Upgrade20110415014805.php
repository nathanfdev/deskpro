<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110415014805 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Changes');

		try {
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `email_gateway_id` INT NULL DEFAULT NULL AFTER  `organization_id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
