<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110922101857 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `pretickets_content`");
			App::getDb()->exec("CREATE TABLE pretickets_content (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, department_id INT NOT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:array)', is_solved TINYINT(1) NOT NULL, object_type VARCHAR(100) DEFAULT NULL, object_id INT DEFAULT NULL, date_created DATETIME NOT NULL, INDEX IDX_E1110A25217BBB47 (person_id), INDEX IDX_E1110A2570BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE pretickets_content ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
			App::getDb()->exec("ALTER TABLE pretickets_content ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
