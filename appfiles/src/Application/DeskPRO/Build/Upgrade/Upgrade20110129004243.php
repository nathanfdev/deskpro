<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110129004243 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add fields to blob table');

		try {
			App::getDb()->exec("ALTER TABLE  `blobs` ADD  `authcode` VARCHAR( 20 ) NOT NULL AFTER  `content_type`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
