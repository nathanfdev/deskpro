<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111123214215 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS permissions");
			App::getDb()->exec("CREATE TABLE permissions (id INT AUTO_INCREMENT NOT NULL, usergroup_id INT DEFAULT NULL, person_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, value LONGTEXT DEFAULT NULL, INDEX IDX_2DEDCC6FD2112630 (usergroup_id), INDEX IDX_2DEDCC6F217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE permissions ADD CONSTRAINT FK_2DEDCC6FD2112630 FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE permissions ADD CONSTRAINT FK_2DEDCC6F217BBB47 FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
