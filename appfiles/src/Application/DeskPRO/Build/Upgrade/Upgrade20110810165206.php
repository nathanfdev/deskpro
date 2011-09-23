<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110810165206 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE person_activity (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, action_type VARCHAR(255) NOT NULL, details LONGTEXT NOT NULL COMMENT '(DC2Type:array)', date_created DATETIME NOT NULL, INDEX IDX_3832AC6D217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE person_activity ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
