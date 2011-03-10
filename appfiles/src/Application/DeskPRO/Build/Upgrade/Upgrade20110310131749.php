<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110310131749 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Update Twitter Accounts');

		try {
			App::getDb()->exec("ALTER TABLE twitter_accounts CHANGE id id BIGINT( 20 ) NOT NULL AUTO_INCREMENT");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
