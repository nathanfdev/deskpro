<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110914110530 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add language_package col to languages table');

		try {
			App::getDb()->exec("ALTER TABLE  `languages` ADD  `language_package` VARCHAR( 255 ) NOT NULL AFTER  `parent_id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
