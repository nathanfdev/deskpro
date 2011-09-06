<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110906095636 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Update filter vocab');

		try {
			App::getDb()->exec("UPDATE ticket_filters SET title = 'My Tickets' WHERE sys_name = 'agent'");
			App::getDb()->exec("UPDATE ticket_filters SET title = 'My Team\\'s Tickets' WHERE sys_name = 'agent_team'");
			App::getDb()->exec("UPDATE ticket_filters SET title = 'Tickets I Follow' WHERE sys_name = 'participant'");
			App::getDb()->exec("UPDATE ticket_filters SET title = 'Unassigned Tickets' WHERE sys_name = 'unassigned'");
			App::getDb()->exec("UPDATE ticket_filters SET title = 'All Tickets' WHERE sys_name = 'all'");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
