<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110212051800 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create people_emails table');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS sessions");
			App::getDb()->exec("
				CREATE TABLE sessions (id INT AUTO_INCREMENT NOT NULL, auth VARCHAR(15) NOT NULL, person_id INT DEFAULT NULL, data LONGTEXT NOT NULL, is_person TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, date_last DATETIME NOT NULL, INDEX date_last_idx (date_last, is_person), PRIMARY KEY(id)) ENGINE = InnoDB
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
