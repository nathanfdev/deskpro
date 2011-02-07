<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110207183839 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add ticket_workflows table');

		try {
			App::getDb()->exec("CREATE TABLE ticket_workflows (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Add workflow_id to tickets table');

		try {
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `workflow_id` INT NULL DEFAULT NULL AFTER  `priority_id`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step3()
	{
		$this->output->writeln('Add some example workflows');

		try {
			App::getDb()->exec("INSERT INTO `ticket_workflows` (`title`) VALUES ('Workflow 1'), ('Workflow 2'), ('Workflow 3')");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
