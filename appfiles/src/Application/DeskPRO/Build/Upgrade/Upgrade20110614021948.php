<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110614021948 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("
				ALTER TABLE  `visitors` ADD  `landing_page` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `user_agent` ,
				ADD  `last_page` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `landing_page`
			");
			App::getDb()->exec("ALTER TABLE  `visitors` ADD  `ref_page` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `user_agent`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
