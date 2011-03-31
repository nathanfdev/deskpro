<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110331192754 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add Twitter stream table');

		try {
			App::getDb()->exec("CREATE TABLE twitter_stream (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, event VARCHAR(32) NOT NULL, data LONGTEXT NOT NULL, date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
