<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110404053032 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create access codes table');

		try {
			App::getDb()->exec("CREATE TABLE ticket_access_codes (ticket_id INT NOT NULL, person_id INT NOT NULL, code VARCHAR(5) NOT NULL, INDEX IDX_CCEE41B5700047D2 (ticket_id), INDEX IDX_CCEE41B5217BBB47 (person_id), PRIMARY KEY(ticket_id, person_id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
