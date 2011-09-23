<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110823152531 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("
				ALTER TABLE  `downloads` ADD  `language_id` INT NULL DEFAULT NULL ,
				ADD  `view_count` INT NOT NULL DEFAULT  '0',
				ADD  `total_rating` INT NOT NULL DEFAULT  '0',
				ADD  `num_ratings` INT NOT NULL DEFAULT  '0',
				ADD  `status` VARCHAR( 15 ) NOT NULL DEFAULT  'published',
				ADD  `hidden_status` VARCHAR( 15 ) NULL DEFAULT NULL ,
				ADD  `date_published` DATETIME NULL DEFAULT NULL
			");
			App::getDb()->exec("
				ALTER TABLE  `news` ADD  `language_id` INT NULL DEFAULT NULL ,
				ADD  `view_count` INT NOT NULL DEFAULT  '0',
				ADD  `total_rating` INT NOT NULL DEFAULT  '0',
				ADD  `num_ratings` INT NOT NULL DEFAULT  '0',
				ADD  `status` VARCHAR( 15 ) NOT NULL DEFAULT  'published',
				ADD  `hidden_status` VARCHAR( 15 ) NULL DEFAULT NULL ,
				ADD  `date_published` DATETIME NULL DEFAULT NULL
			");
			App::getDb()->exec("
				ALTER TABLE  `news` ADD  `slug` VARCHAR( 100 ) NOT NULL DEFAULT  ''
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
