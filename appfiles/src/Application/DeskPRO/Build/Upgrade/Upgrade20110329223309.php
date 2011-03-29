<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110329223309 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Rereate tickets_deleted');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS tickets_deleted");
			App::getDb()->exec("CREATE TABLE tickets_deleted (ticket_id INT NOT NULL, new_ticket_id INT NOT NULL, by_person_id INT NOT NULL, date_created DATETIME NOT NULL, reason VARCHAR(1000) NOT NULL, PRIMARY KEY(ticket_id, new_ticket_id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
