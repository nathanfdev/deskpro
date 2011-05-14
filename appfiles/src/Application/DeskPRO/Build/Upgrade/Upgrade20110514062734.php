<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110514062734 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Dont need handler_class field');

		try {
			App::getDb()->exec("ALTER TABLE  `ticket_page_display` DROP  `handler_class`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
