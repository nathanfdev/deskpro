<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110310134703 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Update Twitter User table');

		try {
			App::getDb()->exec("ALTER TABLE twitter_users CHANGE location location VARCHAR( 255 ) NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
