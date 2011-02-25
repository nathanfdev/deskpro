<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110225175934 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create agent_access');

		try {
			App::getDb()->exec("CREATE TABLE agent_access (person_id INT NOT NULL, access_agent TINYINT(1) NOT NULL, access_admin TINYINT(1) NOT NULL, access_billing TINYINT(1) NOT NULL, access_reports TINYINT(1) NOT NULL, PRIMARY KEY(person_id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Create agent_department_members');

		try {
			App::getDb()->exec("CREATE TABLE agent_department_members (person_id INT NOT NULL, department_id INT NOT NULL, INDEX agent_department_members_person_id_idx (person_id), INDEX agent_department_members_department_id_idx (department_id), PRIMARY KEY(person_id, department_id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
