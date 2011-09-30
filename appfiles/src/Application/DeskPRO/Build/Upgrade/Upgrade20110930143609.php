<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110930143609 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE permissions (id INT AUTO_INCREMENT NOT NULL, usergroup_id INT DEFAULT NULL, person_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, data LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_2DEDCC6FD2112630 (usergroup_id), UNIQUE INDEX UNIQ_2DEDCC6F217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE permissions ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE permissions ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
