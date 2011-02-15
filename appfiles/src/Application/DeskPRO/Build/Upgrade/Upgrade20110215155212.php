<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110215155212 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Recreate labels def table');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `label_defs`");
			App::getDb()->exec("
				CREATE TABLE label_defs (label_type VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(label_type, label)) ENGINE = InnoDB
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
