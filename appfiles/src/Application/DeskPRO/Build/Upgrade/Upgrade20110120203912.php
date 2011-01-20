<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110120203912 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Updates twitter_accounts table');

                try {
			App::getDb()->beginTransaction();
			App::getDb()->exec("
				ALTER TABLE `twitter_accounts` ADD `deleted` TINYINT( 1 ) NULL DEFAULT NULL
			");
			App::getDb()->exec("
				ALTER TABLE `twitter_accounts` CHANGE `access_token` `access_token` TEXT NOT NULL 
			");
			App::getDb()->commit();
		} catch (\Exception $e) {
			$this->writeln("Error: {$e->getMessage()}");
			App::getDb()->rollBack();
			return Updateder::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
