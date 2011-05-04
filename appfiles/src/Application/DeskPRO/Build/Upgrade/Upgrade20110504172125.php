<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110504172125 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add importance fields');

		try {
			App::getDb()->exec("ALTER TABLE  `people` ADD  `importance` TINYINT NOT NULL DEFAULT  '0' AFTER  `is_agent_confirmed`");
			App::getDb()->exec("ALTER TABLE  `organizations` ADD  `importance` TINYINT NOT NULL DEFAULT  '0'");
		} catch (\Exception $e) {}

		return Upgrader::STEP_DONE;
	}
}
