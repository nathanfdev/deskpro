<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120222204235 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE `chat_conversations` ADD `rating_response_time` INT  NULL  DEFAULT NULL  AFTER `person_email`");
			App::getDb()->exec("ALTER TABLE `chat_conversations` ADD `rating_overall` INT  NULL  DEFAULT NULL  AFTER `rating_response_time`");
			App::getDb()->exec("ALTER TABLE `chat_conversations` ADD `rating_comment` TEXT  NULL  AFTER `rating_overall`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
