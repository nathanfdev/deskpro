<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110218000413 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create plugins table');

		try {
			App::getDb()->exec("CREATE TABLE plugins (id INT AUTO_INCREMENT NOT NULL, event_name VARCHAR(255) DEFAULT NULL, associated_object VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, plugin_callback VARCHAR(255) NOT NULL, callback_options LONGTEXT NOT NULL, date_created DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE ticket_triggers (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, event_trigger VARCHAR(50) NOT NULL, event_trigger_option VARCHAR(255) NOT NULL, is_enabled TINYINT(1) NOT NULL, terms LONGTEXT NOT NULL, actions LONGTEXT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
