<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111208164138 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE `queue_items` CHANGE `delay_until` `delay_until` DATETIME  NULL");
			App::getDb()->exec("ALTER TABLE `queue_items` CHANGE `timeout_at` `timeout_at` DATETIME  NULL");
			App::getDb()->exec("ALTER TABLE `queue_items` CHANGE `reserved_at` `reserved_at` DATETIME  NULL");
			App::getDb()->exec("
				CREATE TABLE `content_search` (
				  `object_type` varchar(15) NOT NULL DEFAULT '',
				  `object_id` int(11) NOT NULL,
				  `content` longtext NOT NULL,
				  PRIMARY KEY (`object_type`,`object_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
