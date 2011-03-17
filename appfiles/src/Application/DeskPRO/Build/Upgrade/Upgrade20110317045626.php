<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110317045626 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add is_confirmed and is_agent_confirmed');

		try {
			App::getDb()->exec("ALTER TABLE `people` ADD `is_confirmed` TINYINT NOT NULL DEFAULT  '0' AFTER  `is_user`");
			App::getDb()->exec("ALTER TABLE `people` ADD `is_agent_confirmed` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `is_confirmed`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
