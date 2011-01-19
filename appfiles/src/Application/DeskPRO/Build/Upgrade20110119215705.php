<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110119215705 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->write('New Table for Twitter Admin');
                try {
			App::getDb()->exec("CREATE TABLE twitter_accounts (id INT AUTO_INCREMENT NOT NULL, access_token VARCHAR(255) NOT NULL, twitter_handle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("Error: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}
		return Upgrader::STEP_DONE;
	}
}