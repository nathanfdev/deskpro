<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110301005207 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Recreate phrase table');

		try {
			App::getDb()->exec("DROP TABLE  `phrases`");
			App::getDb()->exec("CREATE TABLE phrases (language_id INT NOT NULL, name VARCHAR(255) NOT NULL, groupname VARCHAR(255) DEFAULT NULL, phrase LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX phrases_language_id_idx (language_id), PRIMARY KEY(language_id, name)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
