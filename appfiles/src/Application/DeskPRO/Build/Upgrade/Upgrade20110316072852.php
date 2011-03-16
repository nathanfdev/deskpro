<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110316072852 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Changes to models');

		try {
			App::getDb()->exec("
				ALTER TABLE  `tickets` ADD  `ref` VARCHAR( 25 ) NOT NULL ,
				ADD  `code` VARCHAR( 12 ) NOT NULL DEFAULT  '',
				ADD INDEX (  `ref` )
			");
			App::getDb()->exec("
				ALTER TABLE  `tickets_participants` ADD  `code` VARCHAR( 12 ) NOT NULL DEFAULT  ''
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
