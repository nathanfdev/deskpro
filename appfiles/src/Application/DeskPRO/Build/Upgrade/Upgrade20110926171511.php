<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110926171511 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE people_emails_validating (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, auth VARCHAR(20) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_3277575C217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE people_emails_validating ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `person_email_validating_id` INT NULL DEFAULT NULL AFTER  `person_email_id`");
			App::getDb()->exec("ALTER TABLE tickets ADD FOREIGN KEY (person_email_validating_id) REFERENCES people_emails_validating(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
