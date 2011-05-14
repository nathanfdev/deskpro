<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110514080611 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add feedback table');

		try {
			App::getDb()->exec("CREATE TABLE ticket_feedback (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, message_id INT DEFAULT NULL, person_id INT DEFAULT NULL, rating INT NOT NULL, message LONGTEXT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_5740B8D9700047D2 (ticket_id), INDEX IDX_5740B8D9537A1329 (message_id), INDEX IDX_5740B8D9217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
