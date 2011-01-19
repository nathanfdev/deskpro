<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110119143522 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create agent_teams table');

		try {
			App::getDb()->exec("
				CREATE TABLE agent_teams (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB
			");
		} catch (\Exception $e) {
			$this->output->writeln("Error: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}
		
		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Create agent_team_members table');

		try {
			App::getDb()->exec("
				CREATE TABLE agent_team_members (person_id INT NOT NULL, team_id INT NOT NULL, INDEX agent_team_members_person_id_idx (person_id), INDEX agent_team_members_team_id_idx (team_id), PRIMARY KEY(person_id, team_id)) ENGINE = InnoDB
			");
		} catch (\Exception $e) {
			$this->output->writeln("Error: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step3()
	{
		$this->output->writeln('Add agent_team_id to tickets table');

		try {
			App::getDb()->exec("ALTER TABLE agent_team_members ADD FOREIGN KEY (person_id) REFERENCES people(id)");
			App::getDb()->exec("ALTER TABLE agent_team_members ADD FOREIGN KEY (team_id) REFERENCES agent_teams(id)");
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `agent_team_id` INT NULL DEFAULT NULL AFTER  `agent_id`");
		} catch (\Exception $e) {
			$this->output->writeln("Error: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
