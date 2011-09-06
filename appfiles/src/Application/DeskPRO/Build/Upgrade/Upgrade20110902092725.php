<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110902092725 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add is_content_html');

		try {
			App::getDb()->exec("ALTER TABLE  `chat_messages` ADD  `is_html` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `is_user_hidden`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
