<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110618021315 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("
				INSERT INTO `idea_votes` VALUES
				(1, 1, 20001, NULL, '', NULL, NULL, 3, 1, '2011-06-17 00:00:00'),
				(2, 1, NULL, 16, '99.231.152.30', NULL, NULL, 2, 1, '2011-06-16 22:12:37')
			");
			App::getDb()->exec("UPDATE `ideas` SET  `num_votes` =  '5' WHERE  `ideas`.`id` =1");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
