<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110406050941 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create cache table');

		try {
			App::getDb()->exec("CREATE TABLE cache (id VARCHAR(100) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:array)', date_expire DATETIME DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
