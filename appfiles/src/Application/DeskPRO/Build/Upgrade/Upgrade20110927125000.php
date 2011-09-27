<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110927125000 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Recreate usersource tables');

		try {
			App::getDb()->exec("CREATE TABLE usersources (id INT AUTO_INCREMENT NOT NULL, note LONGTEXT NOT NULL, title VARCHAR(255) NOT NULL, adapter_class VARCHAR(255) NOT NULL, options LONGTEXT NOT NULL COMMENT '(DC2Type:array)', display_order INT NOT NULL, is_enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE person_usersource_assoc (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, usersource_id INT DEFAULT NULL, identity VARCHAR(255) NOT NULL, identity_friendly VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:array)', created_at DATETIME NOT NULL, INDEX IDX_72215949217BBB47 (person_id), UNIQUE INDEX UNIQ_722159495B71BD01 (usersource_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE person_usersource_assoc ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE person_usersource_assoc ADD FOREIGN KEY (usersource_id) REFERENCES usersources(id) ON DELETE CASCADE");

		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
