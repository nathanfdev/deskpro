<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110406121919 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE twitter_statuses ADD retweet_id BIGINT AFTER user_id");
			App::getDb()->exec("ALTER TABLE twitter_statuses ADD INDEX (retweet_id)");
			App::getDb()->exec("ALTER TABLE twitter_statuses ADD FOREIGN KEY (retweet_id) REFERENCES twitter_statuses(id) ON DELETE CASCADE ON UPDATE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
