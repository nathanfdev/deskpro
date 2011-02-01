<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110201165326 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add gravatar_url and date_picture_check to people table');

		try {
			App::getDb()->exec("ALTER TABLE  `people` ADD  `gravatar_url` VARCHAR( 150 ) NOT NULL DEFAULT  '' AFTER  `picture_blob_id`");
			App::getDb()->exec("ALTER TABLE  `people` ADD  `date_picture_check` DATETIME NULL DEFAULT NULL AFTER  `date_picture`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
