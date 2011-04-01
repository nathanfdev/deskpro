<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110401093710 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('TRUNCATE twitter_accounts_follow* tables and add UNIQUE indexes on twitter_accounts_follow* tables');

		try {
			App::getDb()->exec("TRUNCATE TABLE twitter_accounts_followers");
			App::getDb()->exec("TRUNCATE TABLE twitter_accounts_following");
			App::getDb()->exec("CREATE UNIQUE INDEX account_user_idx ON twitter_accounts_followers (account_id, user_id)");
			App::getDb()->exec("CREATE UNIQUE INDEX account_user_idx ON twitter_accounts_following (account_id, user_id)");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
