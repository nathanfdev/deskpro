<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110223182112 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create department_ticket_display table');

		try {
			App::getDb()->exec("CREATE TABLE department_ticket_display (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, element_type VARCHAR(50) NOT NULL, element_id INT NOT NULL, initial_state VARCHAR(50) NOT NULL, conds_all LONGTEXT NOT NULL, conds_any LONGTEXT NOT NULL, is_agent_only TINYINT(1) NOT NULL, display_order INT NOT NULL, INDEX department_ticket_display_department_id_idx (department_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
