<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110402142422 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Rename Twitter Account Following table to Friends');

		try {
			App::getDb()->exec("RENAME TABLE twitter_accounts_following TO twitter_accounts_friends");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
