<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110524180337 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Alter blobs');

		try {
			App::getDb()->exec("
				ALTER TABLE  `blobs` ADD  `is_media_upload` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `authcode` ,
				ADD  `title` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `is_media_upload`
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
