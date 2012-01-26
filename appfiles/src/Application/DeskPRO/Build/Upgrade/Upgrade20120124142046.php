<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120124142046 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE import_map (typename VARBINARY(80) NOT NULL, old_id VARBINARY(80) NOT NULL, new_id VARBINARY(80) NOT NULL, PRIMARY KEY(typename, old_id)) ENGINE = InnoDB");
			//App::getDb()->exec("ALTER TABLE `people` ADD `password_scheme` VARCHAR(20)  NULL  DEFAULT NULL  AFTER `password`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
