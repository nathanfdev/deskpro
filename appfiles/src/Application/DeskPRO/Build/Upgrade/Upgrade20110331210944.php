<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110331210944 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Update Twitter Stream table');

		try {
			App::getDb()->exec("ALTER TABLE twitter_stream ADD account_id BIGINT NOT NULL AFTER id");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
