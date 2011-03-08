<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110308065403 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Rename queue tables');

		try {
			App::getDb()->exec("RENAME TABLE ticket_queues TO ticket_filters");
			App::getDb()->exec("RENAME TABLE ticket_queues_perms TO ticket_filters_perms");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
