<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111010080502 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, assigned_agent_id INT DEFAULT NULL, assigned_agent_team_id INT DEFAULT NULL, is_completed TINYINT(1) NOT NULL, title LONGTEXT NOT NULL, visibility INT NOT NULL, date_due DATE DEFAULT NULL, date_created DATETIME NOT NULL, date_completed DATETIME DEFAULT NULL, INDEX IDX_50586597217BBB47 (person_id), INDEX IDX_5058659749197702 (assigned_agent_id), INDEX IDX_50586597410D1341 (assigned_agent_team_id), PRIMARY KEY(id)) ENGINE = InnoDB");
                        App::getDb()->exec("ALTER TABLE tasks ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
                        App::getDb()->exec("ALTER TABLE tasks ADD FOREIGN KEY (assigned_agent_id) REFERENCES people(id) ON DELETE SET NULL");
                        App::getDb()->exec("ALTER TABLE tasks ADD FOREIGN KEY (assigned_agent_team_id) REFERENCES agent_teams(id) ON DELETE CASCADE");
                        App::getDb()->exec("CREATE TABLE labels_tasks (task_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_3557E9528DB60186 (task_id), PRIMARY KEY(task_id, label)) ENGINE = InnoDB");
                        App::getDb()->exec("ALTER TABLE labels_tasks ADD FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE");
                        App::getDb()->exec("CREATE TABLE task_associations (id INT AUTO_INCREMENT NOT NULL, task_id INT DEFAULT NULL, person_id INT DEFAULT NULL, ticket_id INT DEFAULT NULL, organization_id INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_41B0E09C8DB60186 (task_id), INDEX IDX_41B0E09C217BBB47 (person_id), INDEX IDX_41B0E09C700047D2 (ticket_id), INDEX IDX_41B0E09C32C8A3DE (organization_id), PRIMARY KEY(id)) ENGINE = InnoDB");
                        App::getDb()->exec("ALTER TABLE task_associations ADD FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE");
                        App::getDb()->exec("ALTER TABLE task_associations ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
                        App::getDb()->exec("ALTER TABLE task_associations ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE");
                        App::getDb()->exec("CREATE TABLE task_comments (id INT AUTO_INCREMENT NOT NULL, task_id INT NOT NULL, person_id INT DEFAULT NULL, content LONGTEXT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_1F5E7C668DB60186 (task_id), INDEX IDX_1F5E7C66217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
                        App::getDb()->exec("ALTER TABLE task_comments ADD FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE");
                        App::getDb()->exec("ALTER TABLE task_comments ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");

                        


		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
