<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110725045842 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("
				CREATE TABLE download_comments (
					id INT AUTO_INCREMENT NOT NULL,
					download_id INT DEFAULT NULL,
					person_id INT DEFAULT NULL,
					visitor_id INT DEFAULT NULL,
					ip_address VARCHAR(30) NOT NULL,
					email VARCHAR(255) DEFAULT NULL,
					name VARCHAR(255) DEFAULT NULL,
					content LONGTEXT NOT NULL,
					status VARCHAR(30) NOT NULL,
					date_created DATETIME NOT NULL,
					INDEX IDX_B43CDE14C667AEAB (download_id),
					INDEX IDX_B43CDE14217BBB47 (person_id),
					INDEX IDX_B43CDE1470BEE6D (visitor_id),
					PRIMARY KEY(id)
				) ENGINE = InnoDB
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
