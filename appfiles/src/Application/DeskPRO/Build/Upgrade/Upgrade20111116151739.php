<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111116151739 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 0");
			App::getDb()->exec("DELETE FROM phrases");
			App::getDb()->exec("DROP TABLE phrases");
			App::getDb()->exec("CREATE TABLE phrases (id INT AUTO_INCREMENT NOT NULL, language_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, groupname VARCHAR(255) DEFAULT NULL, phrase LONGTEXT NOT NULL, original_hash VARCHAR(40) NOT NULL, is_outdated TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_121AC8C682F1BAF4 (language_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE phrases ADD CONSTRAINT FK_121AC8C682F1BAF4 FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE");
			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 1");
		} catch (\Exception $e) {

		}

		return Upgrader::STEP_DONE;
	}
}
