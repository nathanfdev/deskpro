<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110224073607 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add note to widgets table');

		try {
			App::getDb()->exec("ALTER TABLE  `widgets` ADD  `note` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `name_id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
