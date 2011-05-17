<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110517020208 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add js_class to def tables');

		try {
			App::getDb()->exec("ALTER TABLE  `custom_def_ticket` ADD  `js_class` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `id`");
			App::getDb()->exec("ALTER TABLE  `custom_def_people` ADD  `js_class` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
