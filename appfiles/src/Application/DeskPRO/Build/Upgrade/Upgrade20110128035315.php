<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110128035315 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add picture_blob_id to people table');

		try {
			App::getDb()->exec("ALTER TABLE  `people` ADD  `picture_blob_id` INT NULL DEFAULT NULL AFTER  `primary_email_id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;

		return Upgrader::STEP_DONE;
	}
}
