<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110128024923 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add urgency to ticket table');

		try {
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `urgency` INT NOT NULL DEFAULT  '0' AFTER  `status`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Setting random urgency levels');

		try {
			App::getDb()->beginTransaction();
			App::getDb()->exec("UPDATE tickets SET urgency = FLOOR(1 + (RAND() * 99))");
			App::getDb()->commit();
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			App::getDb()->rollback();
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
