<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110310125728 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Update Twitter tables');

		try {
			App::getDb()->exec("ALTER TABLE twitter_accounts ADD oauth_token VARCHAR( 4000 ) NOT NULL, ADD oauth_token_secret VARCHAR( 4000 ) NOT NULL");
			App::getDb()->exec("ALTER TABLE twitter_users DROP geo_latitude, DROP geo_longitude");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
