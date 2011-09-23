<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110816140210 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add notify_template to tickets');

		try {
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `notify_template` VARCHAR( 200 ) NOT NULL AFTER  `creation_system`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
