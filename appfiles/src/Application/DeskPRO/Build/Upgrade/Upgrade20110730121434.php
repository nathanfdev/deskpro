<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110730121434 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE  `tickets_messages` ADD  `email_source_id` INT NULL DEFAULT NULL AFTER  `person_id`");
			App::getDb()->exec("ALTER TABLE  `tickets_messages` ADD  `creation_system` VARCHAR( 20 ) NOT NULL DEFAULT  'web' AFTER  `is_agent_note`");
			App::getDb()->exec("ALTER TABLE  `tickets_messages` ADD  `visitor_id` INT NULL DEFAULT NULL AFTER  `email_source_id`");
			App::getDb()->exec("ALTER TABLE  `tickets_messages` ADD  `ip_address` VARCHAR( 30 ) NOT NULL DEFAULT  '' AFTER  `visitor_id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
