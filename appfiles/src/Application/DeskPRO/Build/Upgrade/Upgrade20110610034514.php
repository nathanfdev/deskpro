<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110610034514 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE  `chat_conversations` ADD  `session_id` INT NULL DEFAULT NULL AFTER  `person_id`");
			App::getDb()->exec("ALTER TABLE  `chat_conversations` ADD  `person_name` VARCHAR( 255 ) NOT NULL AFTER  `visitor_id` , ADD  `person_email` VARCHAR( 255 ) NOT NULL AFTER  `person_name`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
