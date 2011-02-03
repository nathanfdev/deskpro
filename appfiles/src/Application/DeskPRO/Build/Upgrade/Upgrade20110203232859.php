<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110203232859 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Alter widget table (nullable js_widget_class)');

		try {
			App::getDb()->exec("ALTER TABLE  `widgets` CHANGE  `js_widget_class`  `js_widget_class` VARCHAR( 200 ) NULL DEFAULT NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Alter widget table (new php_widget_class)');

		try {
			App::getDb()->exec("ALTER TABLE  `widgets` ADD  `php_widget_class` VARCHAR( 200 ) NULL DEFAULT NULL AFTER  `js_widget_class`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
