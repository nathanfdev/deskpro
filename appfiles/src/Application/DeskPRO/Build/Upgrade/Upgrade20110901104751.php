<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110901104751 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE  `people` ADD  `creation_system` VARCHAR( 20 ) NOT NULL DEFAULT  'web.person' AFTER  `is_agent_confirmed`");
			App::getDb()->exec("ALTER TABLE  `people` ADD  `is_autoresponder` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `is_agent_confirmed`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
