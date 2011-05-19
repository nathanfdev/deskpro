<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110519024504 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Fix changes to org def');

		try {
			App::getDb()->exec("ALTER TABLE  `custom_def_organizations` ADD  `js_class` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `id`");
			App::getDb()->exec("ALTER TABLE  `custom_def_organizations` ADD  `has_form_template` TINYINT( 1 ) NOT NULL DEFAULT  '0',
				ADD  `has_display_template` TINYINT( 1 ) NOT NULL DEFAULT  '0'");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
