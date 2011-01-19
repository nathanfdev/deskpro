<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110119190738 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->write('Update statues in ticket table');
		
		try {
			App::getDb()->beginTransaction();
			App::getDb()->exec("
				UPDATE tickets
				SET status = 'open'
				WHERE status = 'awaiting_agent' OR status = 'awaiting_tech'
			");
			App::getDb()->exec("
				UPDATE tickets
				SET status = 'pending'
				WHERE status = 'awaiting_user'
			");
			App::getDb()->commit();
		} catch (\Exception $e) {
			$this->writeln("Error: {$e->getMessage()}");
			App::getDb()->rollBack();
			return Updateder::STEP_FAILED;
		}
		
		return Upgrader::STEP_DONE;
	}
}
