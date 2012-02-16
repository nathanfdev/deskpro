<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111018143823 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE ticket_filter_subscriptions (filter_id INT NOT NULL, person_id INT NOT NULL, email_new TINYINT(1) NOT NULL, email_user_activity TINYINT(1) NOT NULL, email_agent_activity TINYINT(1) NOT NULL, email_property_change TINYINT(1) NOT NULL, alert_new TINYINT(1) NOT NULL, alert_user_activity TINYINT(1) NOT NULL, alert_agent_activity TINYINT(1) NOT NULL, alert_property_change TINYINT(1) NOT NULL, INDEX IDX_13669D98D395B25E (filter_id), INDEX IDX_13669D98217BBB47 (person_id), PRIMARY KEY(filter_id, person_id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE ticket_filter_subscriptions ADD FOREIGN KEY (filter_id) REFERENCES ticket_filters(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE ticket_filter_subscriptions ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
