<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110610065437 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("
				ALTER TABLE  `chat_messages` ADD  `is_sys` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `content` ,
				ADD  `is_user_hidden` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `is_sys`
			");
			App::getDb()->exec("
				ALTER TABLE  `chat_conversations` ADD  `department_id` INT NULL DEFAULT NULL AFTER  `id`
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
