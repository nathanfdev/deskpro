<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110319075435 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add organization_notes table');

		try {
			App::getDb()->exec("CREATE TABLE organization_notes (id INT AUTO_INCREMENT NOT NULL, organization_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, date_created DATETIME NOT NULL, note VARCHAR(255) NOT NULL, INDEX organization_notes_organization_id_idx (organization_id), INDEX organization_notes_agent_id_idx (agent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
