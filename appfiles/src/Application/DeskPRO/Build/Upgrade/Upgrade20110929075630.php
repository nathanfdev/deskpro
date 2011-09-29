<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110929075630 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			/*
			App::getDb()->exec("
				ALTER TABLE  `departments` ADD  `is_tickets_enabled` TINYINT( 1 ) NOT NULL DEFAULT  '1' AFTER  `parent_id` ,
				ADD  `is_chat_enabled` TINYINT( 1 ) NOT NULL DEFAULT  '1' AFTER  `is_tickets_enabled`
			");
			*/
			App::getDb()->exec("CREATE TABLE department_permissions (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, agent_team_id INT DEFAULT NULL, usergroup_id INT DEFAULT NULL, person_id INT DEFAULT NULL, apply_type VARCHAR(20) NOT NULL, INDEX IDX_84C36B30AE80F5DF (department_id), INDEX IDX_84C36B30FB3FBA04 (agent_team_id), INDEX IDX_84C36B30D2112630 (usergroup_id), INDEX IDX_84C36B30217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE department_permissions ADD FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE department_permissions ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE department_permissions ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
