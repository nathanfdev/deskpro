<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110817133502 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Reset people_prefs');

		try {
			App::getDb()->exec("DROP TABLE `people_prefs`");
			App::getDb()->exec("CREATE TABLE people_prefs (person_id INT NOT NULL, name VARCHAR(255) NOT NULL, value_str LONGTEXT DEFAULT NULL, value_array LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', date_expire DATETIME DEFAULT NULL, INDEX IDX_8112E0E9217BBB47 (person_id), PRIMARY KEY(person_id, name)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE people_prefs ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
