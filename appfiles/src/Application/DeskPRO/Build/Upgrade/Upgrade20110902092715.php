<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110902092715 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add organization2usergroups');

		try {
			App::getDb()->exec("
				CREATE TABLE organization2usergroups (organization_id INT NOT NULL, usergroup_id INT NOT NULL, INDEX IDX_EA8C676432C8A3DE (organization_id), INDEX IDX_EA8C6764D2112630 (usergroup_id), PRIMARY KEY(organization_id, usergroup_id)) ENGINE = InnoDB
			");

			App::getDb()->exec("
				ALTER TABLE  `organizations` ADD  `picture_blob_id` INT NULL DEFAULT NULL AFTER  `id`
			");

			App::getDb()->exec("
				CREATE TABLE organizations_auto_cc (organization_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_864B966432C8A3DE (organization_id), INDEX IDX_864B9664217BBB47 (person_id), PRIMARY KEY(organization_id, person_id)) ENGINE = InnoDB
			");

			App::getDb()->exec("
				ALTER TABLE  `organizations` ADD  `date_created` DATETIME NOT NULL
			");

			App::getDb()->exec("
				UPDATE organizations SET date_created = '2011-09-02 12:05:19'
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
