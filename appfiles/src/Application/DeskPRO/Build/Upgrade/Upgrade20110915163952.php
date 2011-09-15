<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110915163952 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE `people` DROP `locale_id`");
			App::getDb()->exec("ALTER TABLE  `languages` ADD  `locale` VARCHAR( 6 ) NOT NULL DEFAULT  'en_US' AFTER  `parent_id`");
			App::getDb()->exec("DROP TABLE `locales`");
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `language_id` INT NULL DEFAULT NULL AFTER  `id`");
			App::getDb()->exec("ALTER TABLE  `people` ADD  `language_id` INT NULL DEFAULT NULL AFTER  `id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
