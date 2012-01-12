<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120112124340 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Changes to blob table');

		try {
			App::getDb()->exec("ALTER TABLE `blobs` ADD `date_cleanup` DATETIME  NULL  AFTER `date_created`");
			App::getDb()->exec("ALTER TABLE `blobs` ADD `sys_name` VARCHAR(100)  NULL  DEFAULT NULL  AFTER `id`");
			App::getDb()->exec("ALTER TABLE `tickets_attachments` ADD `is_agent_note` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `blob_id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
