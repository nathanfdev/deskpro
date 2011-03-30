<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110330192008 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Modify ticket table');

		try {
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `person_email_id` INT NULL DEFAULT NULL AFTER  `person_id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Set a default ref');

		try {
			App::getDb()->exec("UPDATE tickets SET ref = id WHERE ref = ''");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
