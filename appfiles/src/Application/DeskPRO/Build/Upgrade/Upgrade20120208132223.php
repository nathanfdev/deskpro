<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120208132223 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE `articles` ADD `num_comments` INT  NOT NULL  DEFAULT '0'  AFTER `view_count`");
			App::getDb()->exec("ALTER TABLE `downloads` ADD `num_comments` INT  NOT NULL  DEFAULT '0'  AFTER `view_count`");
			App::getDb()->exec("ALTER TABLE `news` ADD `num_comments` INT  NOT NULL  DEFAULT '0'  AFTER `view_count`");
			App::getDb()->exec("ALTER TABLE `feedback` ADD `num_comments` INT  NOT NULL  DEFAULT '0'  AFTER `view_count`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
