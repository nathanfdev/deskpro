<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110826095950 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS article_ratings");
			App::getDb()->exec("DROP TABLE IF EXISTS ratings");
			App::getDb()->exec("DROP TABLE IF EXISTS searchlog");

			App::getDb()->exec("CREATE TABLE ratings (id INT AUTO_INCREMENT NOT NULL, searchlog_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, object_type VARCHAR(100) NOT NULL, object_id INT NOT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, rating INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_CEB607C9546A72F3 (searchlog_id), INDEX IDX_CEB607C9217BBB47 (person_id), INDEX IDX_CEB607C970BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");

			App::getDb()->exec("CREATE TABLE searchlog (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, query VARCHAR(1000) NOT NULL, num_results INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_8C79CD5C217BBB47 (person_id), INDEX IDX_8C79CD5C70BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE searchlog ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
			App::getDb()->exec("ALTER TABLE searchlog ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
