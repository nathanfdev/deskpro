<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110817081452 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add blob_hash to blobs');

		try {
			App::getDb()->exec("ALTER TABLE  `blobs` ADD  `blob_hash` VARCHAR( 40 ) NOT NULL AFTER  `authcode`");
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `ticket_hash` VARCHAR( 40 ) NOT NULL AFTER  `creation_system`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
