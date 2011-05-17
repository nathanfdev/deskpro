<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110517213340 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add options to ticket display table');

		try {
			App::getDb()->exec("ALTER TABLE  `ticket_page_display` ADD  `options` TEXT NOT NULL DEFAULT  ''");
			App::getDb()->exec("UPDATE ticket_page_display SET options = 'a:0:{}'");
			App::getDb()->exec("DROP TABLE  `phrases`");
			App::getDb()->exec("CREATE TABLE phrases (id INT AUTO_INCREMENT NOT NULL, language_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, groupname VARCHAR(255) DEFAULT NULL, phrase LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_121AC8C682F1BAF4 (language_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
