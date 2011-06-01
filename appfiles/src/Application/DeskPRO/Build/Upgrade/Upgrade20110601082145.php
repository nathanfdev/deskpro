<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110601082145 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add markup mode to articles');

		try {
			App::getDb()->exec("ALTER TABLE  `articles` ADD  `markup_mode` VARCHAR( 15 ) NOT NULL DEFAULT  'markdown' AFTER  `language_id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
