<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110831144323 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `people_contact_data`");
			App::getDb()->exec("DROP TABLE IF EXISTS `organizations_contact_data`");
			App::getDb()->exec("CREATE TABLE people_contact_data (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, contact_type VARCHAR(80) NOT NULL, comment VARCHAR(100) NOT NULL, field_1 LONGTEXT NOT NULL, field_2 LONGTEXT NOT NULL, field_3 LONGTEXT NOT NULL, field_4 LONGTEXT NOT NULL, field_5 LONGTEXT NOT NULL, field_6 LONGTEXT NOT NULL, field_7 LONGTEXT NOT NULL, field_8 LONGTEXT NOT NULL, field_9 LONGTEXT NOT NULL, field_10 LONGTEXT NOT NULL, INDEX IDX_14604ED8217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE people_contact_data ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
			App::getDb()->exec("CREATE TABLE organizations_contact_data (id INT AUTO_INCREMENT NOT NULL, organization_id INT DEFAULT NULL, contact_type VARCHAR(80) NOT NULL, comment VARCHAR(100) NOT NULL, field_1 LONGTEXT NOT NULL, field_2 LONGTEXT NOT NULL, field_3 LONGTEXT NOT NULL, field_4 LONGTEXT NOT NULL, field_5 LONGTEXT NOT NULL, field_6 LONGTEXT NOT NULL, field_7 LONGTEXT NOT NULL, field_8 LONGTEXT NOT NULL, field_9 LONGTEXT NOT NULL, field_10 LONGTEXT NOT NULL, INDEX IDX_25B60D5032C8A3DE (organization_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE organizations_contact_data ADD FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
