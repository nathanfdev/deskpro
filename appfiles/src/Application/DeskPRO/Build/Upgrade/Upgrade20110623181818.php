<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110623181818 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE  `ticket_categories` DROP FOREIGN KEY  `ticket_categories_ibfk_1`");
			App::getDb()->exec("ALTER TABLE  `ticket_categories` DROP FOREIGN KEY  `ticket_categories_ibfk_2`");
			App::getDb()->exec("ALTER TABLE  `ticket_categories` DROP FOREIGN KEY  `ticket_categories_ibfk_3`");
			App::getDb()->exec("ALTER TABLE  `ticket_categories` DROP FOREIGN KEY  `ticket_categories_ibfk_4`");
			App::getDb()->exec("ALTER TABLE  `ticket_categories` DROP FOREIGN KEY  `ticket_categories_ibfk_5`");
			App::getDb()->exec("ALTER TABLE  `ticket_categories` DROP FOREIGN KEY  `ticket_categories_ibfk_6`");
			App::getDb()->exec("ALTER TABLE  `ticket_categories` DROP FOREIGN KEY  `ticket_categories_ibfk_7`");
		} catch (\Exception $e) {}

		try {
			App::getDb()->exec("ALTER TABLE  `ticket_categories` DROP  `department_id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
