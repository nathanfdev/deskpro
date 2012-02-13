<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111111135013 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE `styles` ADD `logo_blob_id` INT NULL DEFAULT NULL AFTER  `note`");
			App::getDb()->exec("ALTER TABLE `styles` ADD FOREIGN KEY ( `parent_id`) REFERENCES `blobs` (`id`) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
