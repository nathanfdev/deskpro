<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110120032505 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add order_by to ticket_queues');

		try {
			App::getDb()->exec("
				ALTER TABLE  `ticket_queues` ADD  `order_by` VARCHAR( 100 ) NOT NULL DEFAULT  '' AFTER  `id`
			");
		} catch (\Exception $e) {
			$this->output->writeln("Error: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}
		
		return Upgrader::STEP_DONE;
	}
}
