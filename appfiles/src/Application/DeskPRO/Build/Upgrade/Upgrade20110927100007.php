<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110927100007 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE  `people_emails_validating` ADD  `validating_content` LONGTEXT NOT NULL AFTER  `auth`");
			App::getDb()->exec("ALTER TABLE  `ideas` ADD  `validating` VARCHAR( 35 ) NULL DEFAULT NULL AFTER  `status`");
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `validating` VARCHAR( 35 ) NULL DEFAULT NULL AFTER  `status`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
