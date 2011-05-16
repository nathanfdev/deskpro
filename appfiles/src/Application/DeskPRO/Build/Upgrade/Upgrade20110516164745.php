<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110516164745 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE  `custom_def_ticket` DROP  `custom_template_id`");
			App::getDb()->exec("ALTER TABLE  `custom_def_people` DROP  `custom_template_id`");
			App::getDb()->exec("
				ALTER TABLE  `custom_def_ticket` ADD  `has_form_template` TINYINT( 1 ) NOT NULL DEFAULT  '0',
				ADD  `has_display_template` TINYINT( 1 ) NOT NULL DEFAULT  '0'
			");
			App::getDb()->exec("
				ALTER TABLE  `custom_def_people` ADD  `has_form_template` TINYINT( 1 ) NOT NULL DEFAULT  '0',
				ADD  `has_display_template` TINYINT( 1 ) NOT NULL DEFAULT  '0'
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
